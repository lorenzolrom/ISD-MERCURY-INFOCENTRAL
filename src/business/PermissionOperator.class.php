<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 12:57 PM
 */


namespace business;


use database\PermissionDatabaseHandler;
use models\Permission;
use models\Role;
use models\Secret;
use models\User;

class PermissionOperator extends Operator
{
    /**
     * @param Role $role
     * @return Permission[]
     * @throws \exceptions\DatabaseException
     */
    public static function getRolePermissions(Role $role): array
    {
        return PermissionDatabaseHandler::selectByRole($role->getId());
    }

    /**
     * @param Secret $secret
     * @return Permission[]
     * @throws \exceptions\DatabaseException
     */
    public static function getSecretPermissions(Secret $secret): array
    {
        return PermissionDatabaseHandler::selectBySecret($secret->getSecret());
    }

    /**
     * @param string $code
     * @return Permission
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getPermission(string $code): Permission
    {
        return PermissionDatabaseHandler::selectByCode($code);
    }

    /**
     * @return Permission[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(): array
    {
        return PermissionDatabaseHandler::select();
    }

    /**
     * @param string $permission
     * @return User[]
     * @throws \exceptions\DatabaseException
     */
    public static function getUsersWithPermission(string $permission): array
    {
        return PermissionDatabaseHandler::selectUsersByPermission($permission);
    }

    /**
     * @param User $user
     * @param string $permission
     * @return Role[]
     * @throws \exceptions\DatabaseException
     */
    public static function getRolesByUserAndPermission(User $user, string $permission): array
    {
        return PermissionDatabaseHandler::selectRolesByUserAndPermission($permission, $user->getId());
    }
}