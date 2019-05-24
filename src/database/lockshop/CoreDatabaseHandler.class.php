<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/24/2019
 * Time: 11:50 AM
 */


namespace database\lockshop;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use models\lockshop\Core;

class CoreDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Core
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Core
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id`, `system`, `code`, `quantity` FROM `LockShop_Core` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('models\lockshop\Core');
    }

    /**
     * @param int $system
     * @param string $code
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectIdByCode(int $system, string $code): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `LockShop_Core` WHERE `system` = :system AND `code` = :code LIMIT 1');
        $select->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $select->bindParam('system', $system, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1 ? $select->fetchColumn() : NULL;
    }

    /**
     * @param int $system
     * @return Core[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectBySystem(int $system): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `LockShop_Core` WHERE `system` = ? LIMIT 1');
        $select->bindParam(1, $system, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $cores = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$cores[] = self::selectById($id);}
            catch(EntryNotFoundException $e){}
        }

        return $cores;
    }

    /**
     * @param int $system
     * @param string $code
     * @param int $quantity
     * @return Core
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $system, string $code, int $quantity): Core
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `LockShop_Core` (`system`, `code`, `quantity`) VALUES (:system, :code, :quantity)');
        $insert->bindParam('system', $system, DatabaseConnection::PARAM_INT);
        $insert->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $insert->bindParam('quantity', $quantity, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $code
     * @param int $quantity
     * @return Core
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $code, int $quantity): Core
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `LockShop_Core` SET `code` = :code, `quantity` = :quantity WHERE `id` = :id');
        $update->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $update->bindParam('quantity', $quantity, DatabaseConnection::PARAM_INT);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);

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

        $delete = $handler->prepare('DELETE FROM `LockShop_Core` WHERE `id` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $key
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectByKey(int $key): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `core` FROM `LockShop_Key_Core` WHERE `key` = ?');
        $select->bindParam(1, $key, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $cores = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$cores[] = self::selectById($id);}
            catch(EntryNotFoundException $e){}
        }

        return $cores;
    }

    /**
     * @param int $id
     * @param int $location
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function assignLocation(int $id, int $location): bool
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `LockShop_Core_Location` (`core`, `location`) VALUES (:core, :location)');
        $insert->bindParam('core', $id, DatabaseConnection::PARAM_INT);
        $insert->bindParam('location', $location, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $handler->close();

        return $insert->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param int $location
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function removeLocation(int $id, int $location): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `LockShop_Core_Location` WHERE `core` = :core AND `location` = :location');
        $delete->bindParam('core', $id, DatabaseConnection::PARAM_INT);
        $delete->bindParam('location', $location, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param int $key
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function assignKey(int $id, int $key): bool
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `LockShop_Key_Core` (`core`, `key`) VALUES (:core, :key)');
        $insert->bindParam('core', $id, DatabaseConnection::PARAM_INT);
        $insert->bindParam('key', $key, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $handler->close();

        return $insert->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param int $key
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function removeKey(int $id, int $key): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `LockShop_Key_Core` WHERE `core` = :core AND `key` = :key');
        $delete->bindParam('core', $id, DatabaseConnection::PARAM_INT);
        $delete->bindParam('key', $key, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }
}