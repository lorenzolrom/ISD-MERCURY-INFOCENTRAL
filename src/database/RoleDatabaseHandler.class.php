<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 10:46 AM
 */


namespace database;


use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use models\Role;

class RoleDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Role
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Role
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id`, `name` FROM `Role` WHERE `id` = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("models\Role");
    }

    /**
     * @param int $userId
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectByUser(int $userId): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `role` FROM `User_Role` WHERE user = ?");
        $select->bindParam(1, $userId, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $roles = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $roles[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $roles;
    }

    /**
     * @param string $name
     * @return Role[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $name): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `Role` WHERE `name` LIKE ?");
        $select->bindParam(1, $name, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $roles = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $roles[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $roles;
    }

    /**
     * @param string $name
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectIdFromName(string $name): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Role` WHERE `name` = ? LIMIT 1');
        $select->bindParam(1, $name, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1 ? $select->fetchColumn() : NULL;
    }

    /**
     * @param string $name
     * @return Role
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $name): Role
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `Role` (`name`) VALUES (:name)');
        $insert->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $name
     * @return Role
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $name): Role
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `Role` SET `name` = :name WHERE `id` = :id');
        $update->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_STR);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `Role` WHERE `id` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param array $permissions
     * @return int
     * @throws DatabaseException
     */
    public static function setPermissions(int $id, array $permissions): int
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `Role_Permission` WHERE `role` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $insert = $handler->prepare('INSERT INTO `Role_Permission` (role, permission) VALUES (:role, :permission)');
        $insert->bindParam('role', $id, DatabaseConnection::PARAM_INT);
        $count = 0;

        foreach($permissions as $permission)
        {
            try
            {
                $insert->bindParam('permission', $permission, DatabaseConnection::PARAM_STR);
                $insert->execute();
                $count++;
            }
            catch(DatabaseException $e){}
        }

        $handler->close();

        return $count;
    }
}