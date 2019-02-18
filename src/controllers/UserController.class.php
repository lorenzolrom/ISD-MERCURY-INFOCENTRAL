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

class UserController extends Controller
{
    /**
     * @param string $uri
     * @return array
     * @throws RouteException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\UserTokenException
     */
    public function processURI(string $uri): array
    {
        $uriParts = explode("/", $uri);

        if($_SERVER['REQUEST_METHOD'] == "GET")
        {
            if (sizeof($uriParts) == 1 AND $uriParts[0] == "") // Get list of users
                return $this->getUsers();
            else if (sizeof($uriParts) == 1) // Get user details
                return $this->getUser(intval($uriParts[0]));
            else if (sizeof($uriParts) == 2 AND $uriParts[1] == "roles") // Get user roles
                return $this->getUserRoles(intval($uriParts[0]));
        }
        else if($_SERVER['REQUEST_METHOD'] == "POST")
        {
            if (sizeof($uriParts) == 1 AND $uriParts[0] == "") // Insert new user
                return $this->createUser();
            else if (sizeof($uriParts) == 2 AND $uriParts[1] == "roles") // Add role to user
                return $this->addRole(intval($uriParts[0]));
        }
        else if($_SERVER['REQUEST_METHOD'] == "PUT")
        {
            if (sizeof($uriParts) == 1) // Get user details
                return $this->updateUser(intval($uriParts[0]));
        }
        else if($_SERVER['REQUEST_METHOD'] == "DELETE")
        {
            if (sizeof($uriParts) == 1) // Delete user
                return $this->deleteUser(intval($uriParts[0]));
            else if (sizeof($uriParts) == 3 AND $uriParts[1] == "roles") // Remove role from user
                return $this->removeRole(intval($uriParts[1]), intval($uriParts[2]));
        }

        throw new RouteException(Messages::ROUTE_URI_NOT_FOUND, RouteException::ROUTE_URI_NOT_FOUND);
    }

    /**
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\UserTokenException
     */
    private function getLoginNames(): array
    {
        FrontController::validatePermission('fa-users-listloginnames');

        return ['loginNames' => UserDatabaseHandler::selectAllLoginNames()];
    }

    /**
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\UserTokenException
     */
    private function getUsers(): array
    {
        FrontController::validatePermission('fa-users-listuserids');

        return ['users' => UserDatabaseHandler::selectAllIDs()];
    }

    /**
     * @param int $userID
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\UserTokenException
     */
    private function getUser(int $userID): array
    {
        FrontController::validatePermission('fa-users-showuserdetails');

        $user = UserFactory::getFromID($userID);

        return ['userDetails' => ['id' => $user->getId(),
                                 'loginName' => $user->getLoginName(),
                                 'authType' => $user->getAuthType(),
                                 'firstName' => $user->getFirstName(),
                                 'lastName' => $user->getLastName(),
                                 'displayName' => $user->getDisplayName(),
                                 'email' => $user->getEmail(),
                                 'disabled' => $user->getDisabled()]];
    }

    /**
     * @param int $userID
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\UserTokenException
     */
    private function getUserRoles(int $userID): array
    {
        FrontController::validatePermission('fa-users-showuserroles');

        $output = array();
        $output['userRoles'] = array();

        $user = UserFactory::getFromID($userID);

        foreach($user->getRoles() as $role)
        {
            $output['userRoles'][] = $role->getId();
        }

        return $output;
    }

    /**
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\UserTokenException
     */
    private function createUser()
    {
        FrontController::validatePermission('fa-users-create');
        return ['request' => 'createUser'];
    }

    /**
     * @param int $userID
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\UserTokenException
     */
    private function updateUser(int $userID): array
    {
        FrontController::validatePermission('fa-users-update');
        return ['request' => 'updateUser: ' . $userID];
    }

    /**
     * @param int $userID
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\UserTokenException
     */
    private function deleteUser(int $userID): array
    {
        FrontController::validatePermission('fa-users-delete');
        return ['request' => 'deleteUser' . $userID];
    }

    /**
     * @param int $userID
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\UserTokenException
     */
    private function addRole(int $userID): array
    {
        FrontController::validatePermission('fa-users-modifyroles');
        return ['request' => 'addRole'];
    }

    /**
     * @param int $userID
     * @param int $roleId
     * @return array
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\UserTokenException
     */
    private function removeRole(int $userID, int $roleId): array
    {
        FrontController::validatePermission('fa-users-modifyroles');
        return ['request' => 'removeRole'];
    }
}