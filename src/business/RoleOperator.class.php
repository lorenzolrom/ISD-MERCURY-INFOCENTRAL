<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 10:50 AM
 */


namespace business;


use database\RoleDatabaseHandler;
use models\Role;
use models\User;

class RoleOperator extends Operator
{
    /**
     * @param int $id
     * @return Role
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getRole(int $id): Role
    {
        return RoleDatabaseHandler::selectById($id);
    }

    /**
     * @param User $user
     * @return Role[]
     * @throws \exceptions\DatabaseException
     */
    public static function getUserRoles(User $user): array
    {
        return RoleDatabaseHandler::selectByUser($user->getId());
    }

    /**
     * @param string $name
     * @return Role[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(string $name = "%"): array
    {
        return RoleDatabaseHandler::select($name);
    }
}