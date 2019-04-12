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
}