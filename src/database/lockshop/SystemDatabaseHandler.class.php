<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/24/2019
 * Time: 11:27 AM
 */


namespace database\lockshop;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use models\lockshop\System;

class SystemDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return System
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): System
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id`, `parent`, `name`, `code`, `master` FROM `LockShop_System` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('models\lockshop\System');
    }

    /**
     * @param string $code
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectIdByCode(string $code): ?string
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `LockShop_System` WHERE `code` = ? LIMIT 1');
        $select->bindParam(1, $code, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1 ? $select->fetchColumn() : NULL;
    }

    /**
     * @param string $code
     * @param string $name
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $code = '%', string $name = '%'): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `LockShop_System` WHERE `name` LIKE :name AND `code` LIKE :code');
        $select->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $select->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $systems = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$systems[] = self::selectById($id);}
            catch(EntryNotFoundException $e){}
        }

        return $systems;
    }

    /**
     * @param string $name
     * @param string $code
     * @param int|null $parent
     * @param int|null $master
     * @return System
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $name, string $code, ?int $parent = NULL, ?int $master = NULL): System
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `LockShop_System` (`parent`, `name`, `code`, `master`) VALUES (:parent, :name, :code, :master)');
        $insert->bindParam('parent', $parent, DatabaseConnection::PARAM_INT);
        $insert->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $insert->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $insert->bindParam('master', $master, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $name
     * @param string $code
     * @param int|null $parent
     * @param int|null $master
     * @return System
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $name, string $code, ?int $parent, ?int $master): System
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `LockShop_System` SET `parent` = :parent, `name` = :name, `code` = :code, `master` = :master WHERE `id` = :id');
        $update->bindParam('parent', $parent, DatabaseConnection::PARAM_INT);
        $update->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $update->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $update->bindParam('master', $master, DatabaseConnection::PARAM_INT);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
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

        $delete = $handler->prepare('DELETE FROM `LockShop_System` WHERE `id` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }
}