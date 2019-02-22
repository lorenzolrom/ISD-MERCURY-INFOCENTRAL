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
use exceptions\EntryNotFoundException;
use exceptions\RouteException;
use factories\PermissionFactory;
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
            else if(sizeof($uriParts) == 2 AND $uriParts[1] == "permissions") // Add permission to role
                return $this->addPermission(intval($uriParts[0]));
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
            else if(sizeof($uriParts) == 3 AND $uriParts[1] == "permissions") // Remove permission from role
                return $this->removePermission(intval($uriParts[0]), $uriParts[2]);
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
        if(!isset($vars['data']['displayName']))
            $errors[] = ['type' => 'validation', 'field' => 'displayName', 'message' => ValidationError::MESSAGE_VALUE_REQUIRED];
        else
        {
            switch (Role::validateDisplayName($vars['data']['displayName'])) {
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

        $validation = $this->validateRole(FrontController::getDocumentAsArray());
        if(!empty($validation))
            return $validation;

        // Create new role
        $role = RoleFactory::getNew(FrontController::getDocumentAsArray()['data']['displayName']);

        http_response_code(201);
        return ['data' => ['type' => 'Role', 'id' => $role->getId()]];
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
        $validation = $this->validateRole(FrontController::getDocumentAsArray());
        if(!empty($validation))
            return $validation;

        // Update role
        $role->setDisplayName(FrontController::getDocumentAsArray()['data']['displayName']);

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

    /**
     * @param int $roleID
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function addPermission(int $roleID): array
    {
        FrontController::validatePermission('fa-roles-modify');

        $role = RoleFactory::getFromID($roleID);

        $errors = array();

        // Validate Submission
        $submission = FrontController::getDocumentAsArray();

        if(!isset($submission['data']['permissionCode']))
            $errors[] = ['type' => 'validation', 'field' => 'permissionCode', 'message' => ValidationError::MESSAGE_VALUE_REQUIRED];
        else
        {
            $permissionCode = $submission['data']['permissionCode'];
            // Check if role already has permission
            if(in_array($permissionCode, $role->getPermissionCodes()))
                $errors[] = ['type' => 'validation', 'field' => 'permissionCode', 'message' => ValidationError::MESSAGE_VALUE_ALREADY_ASSIGNED];
            else
            {
                // Check if permission code exists
                try
                {
                    PermissionFactory::getFromCode($permissionCode);
                }
                catch(EntryNotFoundException $e)
                {
                    $errors[] = ['type' => 'validation', 'field' => 'permissionCode', 'message' => ValidationError::MESSAGE_VALUE_NOT_FOUND];
                }
            }
        }

        // Return errors, if present
        if(!empty($errors))
        {
            http_response_code(409);
            return ['errors' => $errors];
        }

        // Assign permission
        $role->addPermission($submission['data']['permissionCode']);

        http_response_code(204);
        return [];
    }

    /**
     * @param int $roleID
     * @param string $permissionCode
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function removePermission(int $roleID, string $permissionCode): array
    {
        FrontController::validatePermission('fa-roles-modify');

        $role = RoleFactory::getFromID($roleID);

        if(!in_array($permissionCode, $role->getPermissionCodes()))
        {
            throw new EntryNotFoundException(ValidationError::MESSAGE_VALUE_NOT_ASSIGNED, EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);
        }

        $role->removePermission($permissionCode);

        http_response_code(204);
        return [];
    }
}