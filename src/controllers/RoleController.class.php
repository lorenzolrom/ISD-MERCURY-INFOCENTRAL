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

class RoleController extends Controller
{

    /**
     * @param string $uri
     * @return array
     * @throws RouteException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\UserTokenException
     */
    public function processURI(string $uri): array
    {
        $uriParts = explode("/", $uri);

        if(sizeof($uriParts) == 1 AND $uriParts[0] == "") // Get list of roles
            return $this->getRoles();
        else if(sizeof($uriParts) == 1) // Get role details
            return $this->getRole(intval($uriParts[0]));
        else if(sizeof($uriParts) == 2 AND $uriParts[1] == "permissions") // Get role permissions
            return $this->getRolePermissions(intval($uriParts[0]));
        else
            throw new RouteException(Messages::ROUTE_URI_NOT_FOUND, RouteException::ROUTE_URI_NOT_FOUND);
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\UserTokenException
     */
    private function getRoles(): array
    {
        FrontController::validatePermission('fa-roles-listroleids');
        return ['roles' => RoleDatabaseHandler::selectAllIDs()];
    }

    /**
     * @param int $roleId
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\UserTokenException
     */
    private function getRole(int $roleId): array
    {
        FrontController::validatePermission('fa-roles-showroledetails');
        $role = RoleFactory::getFromID($roleId);

        return ['role' => ['id' => $role->getId(), 'displayName' => $role->getDisplayName()]];
    }

    /**
     * @param int $roleId
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\UserTokenException
     */
    private function getRolePermissions(int $roleId): array
    {
        FrontController::validatePermission('fa-roles-showrolepermissions');
        $role = RoleFactory::getFromID($roleId);

        return ['rolePermissions' => $role->getPermissionCodes()];
    }
}