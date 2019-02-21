<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 7:55 PM
 */


namespace controllers;


use database\RoleDatabaseHandler;
use exceptions\RouteException;
use factories\RoleFactory;
use messages\Messages;
use messages\ValidationError;
use models\Role;

class RoleController extends Controller
{

    /**
     * @param string $uri
     * @return array
     * @throws RouteException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public function processURI(string $uri): array
    {
        $uriParts = explode("/", $uri);

        if($_SERVER['REQUEST_METHOD'] == "GET")
        {
            if(sizeof($uriParts) == 1 AND $uriParts[0] == "") // Get list of roles
                return $this->getRoles();
            else if(sizeof($uriParts) == 1) // Get role details
                return $this->getRole(intval($uriParts[0]));
            else if(sizeof($uriParts) == 2 AND $uriParts[1] == "permissions") // Get role permissions
                return $this->getPermissions(intval($uriParts[0]));
        }
        else if($_SERVER['REQUEST_METHOD'] == "POST")
        {
            if(sizeof($uriParts) == 1 AND $uriParts[0] == "") // Create new role
                return $this->createRole();
        }
        else if($_SERVER['REQUEST_METHOD'] == "PUT")
        {
            if(sizeof($uriParts) == 1) // Update role
                return $this->updateRole(intval($uriParts[0]));
        }
        else if($_SERVER['REQUEST_METHOD'] == "DELETE")
        {
            if(sizeof($uriParts) == 1) // Delete role
                return $this->deleteRole(intval($uriParts[0]));
        }

        throw new RouteException(Messages::ROUTE_URI_NOT_FOUND, RouteException::ROUTE_URI_NOT_FOUND);
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function getRoles(): array
    {
        FrontController::validatePermission('fa-roles-listroles');

        $roleIDs = array();

        foreach(RoleDatabaseHandler::selectAllIDs() as $roleID)
        {
            $role = RoleFactory::getFromID($roleID);
            $roleIDs[] = ['type' => 'Role', 'id' => $role->getId(), 'displayName' => $role->getDisplayName()];
        }

        return ['data' => $roleIDs];
    }

    /**
     * @param int $roleId
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function getRole(int $roleId): array
    {
        FrontController::validatePermission('fa-roles-showroledetails');
        $role = RoleFactory::getFromID($roleId);

        return ['data' => ['type' => 'Role', 'id' => $role->getId(), 'displayName' => $role->getDisplayName()]];
    }

    /**
     * @param int $roleId
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function getPermissions(int $roleId): array
    {
        FrontController::validatePermission('fa-roles-showrolepermissions');
        $role = RoleFactory::getFromID($roleId);

        $permissions = array();

        foreach($role->getPermissionCodes() as $permissionCode)
        {
            $permissions[] = ['type' => 'Permission', 'code' => $permissionCode];
        }

        return ['data' => $permissions];
    }

    /**
     * @param array $vars
     * @return array
     * @throws \exceptions\DatabaseException
     */
    private function validateRole(array $vars): array
    {
        $errors = array();

        // Validate Submission
        if(!isset($vars['displayName']))
            $errors[] = ['type' => 'validation', 'field' => 'displayName', 'message' => ValidationError::MESSAGE_VALUE_REQUIRED];
        else
        {
            switch (Role::validateDisplayName($vars['displayName'])) {
                case ValidationError::VALUE_IS_NULL:
                    $errors[] = ['type' => 'validation', 'field' => 'displayName', 'message' => ValidationError::MESSAGE_VALUE_REQUIRED];
                    break;
                case ValidationError::VALUE_ALREADY_TAKEN:
                    $errors[] = ['type' => 'validation', 'field' => 'displayName', 'message' => ValidationError::MESSAGE_VALUE_ALREADY_TAKEN];
                    break;
                case ValidationError::VALUE_IS_TOO_SHORT:
                    $errors[] = ['type' => 'validation', 'field' => 'displayName', 'message' => ValidationError::getLengthMessage(1, 64)];
                    break;
                case ValidationError::VALUE_IS_TOO_LONG:
                    $errors[] = ['type' => 'validation', 'field' => 'displayName', 'message' => ValidationError::getLengthMessage(1, 64)];
                    break;
            }
        }

        // Return errors, if present
        if(!empty($errors))
        {
            http_response_code(409);
            return ['errors' => $errors];
        }

        return [];
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function createRole(): array
    {
        FrontController::validatePermission('fa-roles-create');

        $validation = $this->validateRole($_POST);
        if(!empty($validation))
            return $validation;

        // Create new role
        http_response_code(201);
        return ['data' => ['type' => 'Role', 'id' => RoleFactory::getNew($_POST['displayName'])->getId()]];
    }

    /**
     * @param int $roleID
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function updateRole(int $roleID): array
    {
        FrontController::validatePermission('fa-roles-modify');

        $role = RoleFactory::getFromID($roleID);

        // Validate Submission
        $validation = $this->validateRole(FrontController::getPUTArray());
        if(!empty($validation))
            return $validation;

        // Update role
        $role->setDisplayName(FrontController::getPUTArray()['displayName']);

        // Respond 'OK'
        http_response_code(204);
        return [];
    }

    /**
     * @param int $roleID
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function deleteRole(int $roleID): array
    {
        FrontController::validatePermission('fa-roles-delete');

        $role = RoleFactory::getFromID($roleID);

        // Delete role
        $role->delete();

        // Respond OK
        http_response_code(204);
        return [];
    }
}