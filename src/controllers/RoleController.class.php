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
     * @throws \exceptions\TokenException
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
            else if(sizeof($uriParts) == 3 AND $uriParts[1] == "relationships") // Get role permissions
                return $this->getRelationships(intval($uriParts[0]), $uriParts[2]);
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

        throw new RouteException(Messages::ROUTE_URI_NOT_FOUND, RouteException::ROUTE_URI_NOT_FOUND);
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\TokenException
     */
    private function getRoles(): array
    {
        FrontController::validatePermission('fa-roles-listroleids');

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
     * @throws \exceptions\TokenException
     */
    private function getRole(int $roleId): array
    {
        FrontController::validatePermission('fa-roles-showroledetails');
        $role = RoleFactory::getFromID($roleId);

        return ['data' => ['type' => 'Role', 'id' => $role->getId(), 'displayName' => $role->getDisplayName()]];
    }

    /**
     * @param int $roleId
     * @param string $type
     * @return array
     * @throws RouteException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\TokenException
     */
    private function getRelationships(int $roleId, string $type): array
    {
        if($type == "permissions")
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

        throw new RouteException(Messages::ROUTE_REQUIRED_PARAMETER_IS_INVALID, RouteException::REQUIRED_PARAMETER_IS_INVALID);
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\TokenException
     */
    private function createRole(): array
    {
        FrontController::validatePermission('fa-roles-create');

        $errors = array();

        // Validate Submission
        if(!isset($_POST['displayName']))
            $errors[] = ['type' => 'validation', 'field' => 'displayName', 'message' => ValidationError::MESSAGE_VALUE_REQUIRED];
        else
        {
            switch (Role::validateDisplayName($_POST['displayName'])) {
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
            return ['errors' => $errors];

        // Create new role
        http_response_code(201);
        return ['id' => RoleFactory::getNew($_POST['displayName'])->getId()];
    }

    /**
     * @param int $roleID
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\TokenException
     */
    private function updateRole(int $roleID): array
    {
        FrontController::validatePermission('fa-roles-modify');

        $errors = array();

        $role = RoleFactory::getFromID($roleID);

        // Validate Submission

        // Respond 'OK'
        http_response_code(204);
        return [];
    }
}