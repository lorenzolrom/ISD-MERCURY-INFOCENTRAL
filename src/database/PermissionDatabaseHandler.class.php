<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 12:52 PM
 */


namespace database;


use business\RoleOperator;
use business\UserOperator;
use exceptions\EntryNotFoundException;
use models\Permission;
use models\Role;
use models\User;

class PermissionDatabaseHandler extends DatabaseHandler
{
    /**
     * @param string $code
     * @return Permission
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectByCode(string $code): Permission
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `code` FROM `Permission` WHERE `code` = ?");
        $select->bindParam(1, $code, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("models\Permission");
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function select(): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `code` FROM `Permission`");
        $select->execute();

        $handler->close();

        $permissions = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $code)
        {
            try
            {
                $permissions[] = self::selectByCode($code);
            }
            catch(EntryNotFoundException $e){}
        }

        return $permissions;
    }

    /**
     * @param int $roleId
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectByRole(int $roleId): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `permission` FROM `Role_Permission` WHERE role = ?");
        $select->bindParam(1, $roleId, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $permissions = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $permissionCode)
        {
            try
            {
                $permissions[] = self::selectByCode($permissionCode);
            }
            catch(EntryNotFoundException $e){}
        }

        return $permissions;
    }

    /**
     * @param string $secret
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectBySecret(string $secret): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `permission` FROM `Secret_Permission` WHERE `secret` = ?");
        $select->bindParam(1, $secret, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $permissions = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $permissionCode)
        {
            try
            {
                $permissions[] = self::selectByCode($permissionCode);
            }
            catch(EntryNotFoundException $e){}
        }

        return $permissions;
    }

    /**
     * Select all users with permission
     * @param string $permission
     * @return User[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectUsersByPermission(string $permission): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `User` WHERE `id` IN 
                              (SELECT `user` FROM `User_Role` WHERE `role` IN 
                              (SELECT `role` FROM `Role_Permission` WHERE `permission` = :permission))');
        $select->bindParam('permission', $permission, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $users = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $users[] = UserOperator::getUser($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $users;
    }

    /**
     * @param string $permission
     * @param int $user
     * @return Role[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectRolesByUserAndPermission(string $permission, int $user): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Role` WHERE `id` IN 
                              (SELECT `role` FROM `Role_Permission` WHERE `permission` = :permission) 
                          AND `id` IN (SELECT `role` FROM `User_Role` WHERE `user` = :user)');
        $select->bindParam('permission', $permission, DatabaseConnection::PARAM_STR);
        $select->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $roles = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $roles[] = RoleOperator::getRole($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $roles;
    }
}