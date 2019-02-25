<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 6:35 PM
 */


namespace controllers;


use database\UserDatabaseHandler;
use exceptions\RouteException;
use exceptions\SecurityException;
use factories\UserFactory;
use messages\Messages;
use messages\ValidationError;
use models\User;

class UserController extends Controller
{
    /**
     * @param string $uri
     * @return array
     * @throws RouteException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public function processURI(string $uri): array
    {
        $uriParts = explode("/", $uri);

        if($uriParts[0] == "users")
        {
            if ($_SERVER['REQUEST_METHOD'] == "GET")
            {
                if (sizeof($uriParts) == 1 AND $uriParts[0] == "users") // Get list of users
                    return $this->getUsers();
                else if (sizeof($uriParts) == 2) // Get user details
                    return $this->getUser(intval($uriParts[1]));
                else if (sizeof($uriParts) == 3 AND $uriParts[2] == "roles") // Get user roles
                    return $this->getUserRoles(intval($uriParts[1]));
            }
            else if ($_SERVER['REQUEST_METHOD'] == "POST")
            {
                if (sizeof($uriParts) == 1 AND $uriParts[0] == "users") // Insert new user
                    return $this->createUser();
                else if (sizeof($uriParts) == 3 AND $uriParts[2] == "roles") // Add role to user
                    return $this->addRole(intval($uriParts[1]));
            }
            else if ($_SERVER['REQUEST_METHOD'] == "PUT")
            {
                if (sizeof($uriParts) == 2) // Get user details
                    return $this->updateUser(intval($uriParts[1]));
            }
            else if ($_SERVER['REQUEST_METHOD'] == "DELETE")
            {
                if (sizeof($uriParts) == 2) // Delete user
                    return $this->deleteUser(intval($uriParts[1]));
                else if (sizeof($uriParts) == 4 AND $uriParts[2] == "roles") // Remove role from user
                    return $this->removeRole(intval($uriParts[2]), intval($uriParts[3]));
            }
        }
        else if($uriParts[0] == "currentUser" AND $_SERVER['REQUEST_METHOD'] == "GET") // Retrieve details for current user
        {
            if(sizeof($uriParts) == 1)// Get current user
                return $this->getCurrentUser();
            else if(sizeof($uriParts) == 2 AND $uriParts[1] == "roles") // Get current user roles
                return $this->getCurrentUserRoles();
        }

        throw new RouteException(Messages::ROUTE_URI_NOT_FOUND, RouteException::ROUTE_URI_NOT_FOUND);
    }

    /**
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getUsers(): array
    {
        FrontController::validatePermission('fa-users-listusers');

        $users = array();

        foreach(UserDatabaseHandler::selectAllIDs() as $userID)
        {
            $users[] = ['type' => 'User', 'id' => $userID];
        }

        return ['data' => $users];
    }

    /**
     * @param int $userID
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getUser(int $userID): array
    {
        FrontController::validatePermission('fa-users-showuserdetails');

        $user = UserFactory::getFromID($userID);

        return ['data' => ['type' => 'User',
                                 'id' => $user->getId(),
                                 'loginName' => $user->getLoginName(),
                                 'authType' => $user->getAuthType(),
                                 'firstName' => $user->getFirstName(),
                                 'lastName' => $user->getLastName(),
                                 'displayName' => $user->getDisplayName(),
                                 'email' => $user->getEmail(),
                                 'disabled' => $user->getDisabled()]];
    }

    /**
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getCurrentUser(): array
    {
        return $this->getUser(FrontController::getCurrentUser()->getId());
    }

    /**
     * @param int $userID
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getUserRoles(int $userID): array
    {
        FrontController::validatePermission('fa-users-showuserroles');

        $roles = array();

        $user = UserFactory::getFromID($userID);

        foreach($user->getRoles() as $role)
        {
            $roles[] = ['type' => 'Role', 'id' => $role->getId(), 'displayName' => $role->getDisplayName()];
        }

        return ['data' => $roles];
    }

    /**
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public function getCurrentUserRoles(): array
    {
        return $this->getUserRoles(FrontController::getCurrentUser()->getId());
    }

    /**
     * @param array $vars
     * @param bool $strict If TRUE, null entries will fail validation
     * @return array
     * @throws RouteException
     * @throws \exceptions\DatabaseException
     */
    private function validateUser(array $vars, bool $strict = FALSE): array
    {
        $errors = array();

        if (!isset($vars['data']))
            throw new RouteException(Messages::ROUTE_REQUIRED_PARAMETER_MISSING, RouteException::REQUIRED_DOCUMENT_PARAMETER_MISSING);

        $vars = $vars['data'];

        // loginName
        switch (User::validateLoginName(isset($vars['loginName']) ? $vars['loginName'] : NULL)) {
            case ValidationError::VALUE_IS_NULL:
                if ($strict)
                    $errors[] = ValidationError::getErrorArrayEntry('loginName', ValidationError::MESSAGE_VALUE_REQUIRED);
                break;
            case ValidationError::VALUE_IS_TOO_SHORT:
            case ValidationError::VALUE_IS_TOO_LONG:
                $errors[] = ValidationError::getErrorArrayEntry('loginName', ValidationError::getLengthMessage(1, 64));
                break;
            case ValidationError::VALUE_ALREADY_TAKEN:
                $errors[] = ValidationError::getErrorArrayEntry('loginName', ValidationError::MESSAGE_VALUE_ALREADY_TAKEN);
        }

        // authType
        switch (User::validateAuthType(isset($vars['authType']) ? $vars['authType'] : NULL)) {
            case ValidationError::VALUE_IS_NULL:
                if ($strict)
                    $errors[] = ValidationError::getErrorArrayEntry('authType', ValidationError::MESSAGE_VALUE_REQUIRED);
                break;
            case ValidationError::VALUE_IS_INVALID:
                $errors[] = ValidationError::getErrorArrayEntry('authType', ValidationError::MESSAGE_VALUE_NOT_VALID);
        }

        // firstName & lastName
        foreach (['firstName', 'lastName'] as $name) {
            switch (User::validateXName(isset($vars[$name]) ? $vars[$name] : NULL)) {
                case ValidationError::VALUE_IS_NULL:
                    if ($strict)
                        $errors[] = ValidationError::getErrorArrayEntry($name, ValidationError::MESSAGE_VALUE_REQUIRED);
                    break;
                case ValidationError::VALUE_IS_TOO_SHORT:
                case ValidationError::VALUE_IS_TOO_LONG:
                    $errors[] = ValidationError::getErrorArrayEntry($name, ValidationError::getLengthMessage(1, 32));
            }
        }

        // email
        switch (User::validateEmail(isset($vars['email']) ? $vars['email'] : NULL)) {
            case ValidationError::VALUE_IS_INVALID:
                $errors[] = ValidationError::getErrorArrayEntry('email', ValidationError::MESSAGE_VALUE_NOT_VALID);
        }

        // disabled
        switch (User::validateDisabled(isset($vars['disabled']) ? $vars['disabled'] : NULL)) {
            case ValidationError::VALUE_IS_NULL:
                if ($strict)
                    $errors[] = ValidationError::getErrorArrayEntry('disabled', ValidationError::MESSAGE_VALUE_REQUIRED);
                break;
            case ValidationError::VALUE_IS_INVALID:
                $errors[] = ValidationError::getErrorArrayEntry('disabled', ValidationError::MESSAGE_VALUE_NOT_VALID);
        }

        // Verify password is set for local authentication
        if(isset($vars['authType']) AND $vars['authType'] == "local")
        {
            switch(User::validatePassword(isset($vars['password']) ? $vars['password'] : NULL))
            {
                case ValidationError::VALUE_IS_NULL:
                    $errors [] = ValidationError::getErrorArrayEntry('password', ValidationError::MESSAGE_VALUE_REQUIRED);
                    break;
                case ValidationError::VALUE_IS_TOO_SHORT:
                    $errors[] = ValidationError::getErrorArrayEntry('password', ValidationError::MESSAGE_PASSWORD_TOO_SHORT);
            }
        }

        if (!empty($errors))
        {
            http_response_code(409);
            return ['errors' => $errors];
        }

        return [];
    }

    /**
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws RouteException
     */
    private function createUser(): array
    {
        FrontController::validatePermission('fa-users-create');

        $vars = FrontController::getDocumentAsArray();

        $validation = $this->validateUser($vars, TRUE);

        $vars = $vars['data'];

        // Set values that can be null to null to avoid undefined index warnings
        if(!isset($vars['password']))
            $vars['password'] = NULL;
        if(!isset($vars['displayName']))
            $vars['displayName'] = NULL;
        if(!isset($vars['email']))
            $vars['email'] = NULL;

        if(!empty($validation))
            return $validation;

        $user = UserFactory::getNew($vars['loginName'],
                                    $vars['authType'],
                                    $vars['password'], // NULL
                                    $vars['firstName'],
                                    $vars['lastName'],
                                    $vars['displayName'], // NULL
                                    $vars['email'], // NULL
                                    intval($vars['disabled']));

        http_response_code(201);
        return ['data' => ['type' => 'User', 'id' => $user->getID()]];
    }

    /**
     * @param int $userID
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function updateUser(int $userID): array
    {
        FrontController::validatePermission('fa-users-update');
        http_response_code(501);
        return[];
    }

    /**
     * @param int $userID
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function deleteUser(int $userID): array
    {
        FrontController::validatePermission('fa-users-delete');

        $user = UserFactory::getFromID($userID);
        $user->delete();

        http_response_code(204);
        return[];
    }

    /**
     * @param int $userID
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function addRole(int $userID): array
    {
        FrontController::validatePermission('fa-users-modifyroles');
        http_response_code(501);
        return[];
    }

    /**
     * @param int $userID
     * @param int $roleId
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function removeRole(int $userID, int $roleId): array
    {
        FrontController::validatePermission('fa-users-modifyroles');
        http_response_code(501);
        return[];
    }
}