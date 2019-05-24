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
use utilities\HistoryRecorder;

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

    /**
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function createRole(array $vals): array
    {
        self::validate('models\Role', $vals);

        $role = RoleDatabaseHandler::insert($vals['name']);
        $history = HistoryRecorder::writeHistory('Role', HistoryRecorder::CREATE, $role->getId(), $role);

        if(is_array($vals['permissions']))
        {
            RoleDatabaseHandler::setPermissions($role->getId(), $vals['permissions']);
            HistoryRecorder::writeAssocHistory($history, array('permissions' => $vals['permissions']));
        }

        return array('id' => $role->getId());
    }

    /**
     * @param Role $role
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function updateRole(Role $role, array $vals): array
    {
        self::validateRole($vals, $role);

        $history = HistoryRecorder::writeHistory('Role', HistoryRecorder::MODIFY, $role->getId(), $role, $vals);

        if(is_array($vals['permissions']))
        {
            HistoryRecorder::writeAssocHistory($history, array('permissions' => $vals['permissions']));
            RoleDatabaseHandler::setPermissions($role->getId(), $vals['permissions']);
        }

        $role = RoleDatabaseHandler::update($role->getId(), $vals['name']);

        return array('id' => $role->getId());
    }

    /**
     * @param Role $role
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function deleteRole(Role $role): bool
    {
        return RoleDatabaseHandler::delete($role->getId());
    }

    /**
     * @param array $vals
     * @param Role|null $role
     *
     * Need to override parent validate because of unique name constraint
     *
     * @return bool
     * @throws \exceptions\ValidationError
     */
    protected static function validateRole(array $vals, ?Role $role = NULL): bool
    {
        if($role === NULL OR $role->getName() != $vals['name'])
        {
            return parent::validate('models\Role', $vals);
        }

        return TRUE;
    }
}