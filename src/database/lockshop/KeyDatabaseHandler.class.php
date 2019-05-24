<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/24/2019
 * Time: 11:57 AM
 */


namespace database\lockshop;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use models\lockshop\Key;
use models\lockshop\KeyAssignment;

class KeyDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Key
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Key
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id`, `system`, `bitting`, `quantity` FROM `LockShop_Key` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('models\lockshop\Key');
    }

    /**
     * @param int $system
     * @return Key[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectBySystem(int $system): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `LockShop_Key` WHERE `system` = ? LIMIT 1');
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
     * @param string $bitting
     * @param int $quantity
     * @return Key
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $system, string $bitting, int $quantity): Key
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `LockShop_Key` (`system`, `bitting`, `quantity`) VALUES (:system, :bitting, :quantity)');
        $insert->bindParam('system', $system, DatabaseConnection::PARAM_INT);
        $insert->bindParam('bitting', $bitting, DatabaseConnection::PARAM_STR);
        $insert->bindParam('quantity', $quantity, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $bitting
     * @param int $quantity
     * @return Key
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $bitting, int $quantity): Key
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `LockShop_Key` SET `bitting` = :bitting, `quantity` = :quantity WHERE `id` = :id');
        $update->bindParam('bitting', $bitting, DatabaseConnection::PARAM_STR);
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

        $delete = $handler->prepare('DELETE FROM `LockShop_Key` WHERE `id` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param int $user
     * @param string $serial
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function assignToUser(int $id, int $user, string $serial): bool
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `LockShop_Key_User` (`key`, `user`, `serial`) VALUES (:key, :user, :serial)');
        $insert->bindParam('key', $id, DatabaseConnection::PARAM_INT);
        $insert->bindParam('serial', $serial, DatabaseConnection::PARAM_STR);
        $insert->bindParam('user', $user, DatabaseConnection::PARAM_INT);

        $handler->close();

        return $insert->getRowCount();
    }

    /**
     * @param int $id
     * @return KeyAssignment[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectAssignments(int $id): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `key`, `user`, `serial` FROM `LockShop_Key_User` WHERE `key` = ?');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->fetchAll(DatabaseConnection::FETCH_CLASS, 'models\lockshop\KeyAssignment');
    }

    /**
     * @param int $id
     * @param string $serial
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function removeAssignment(int $id, string $serial): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `LockShop_Key_User` WHERE `key` = :key AND `serial` = :serial');
        $delete->bindParam('key', $id, DatabaseConnection::PARAM_INT);
        $delete->bindParam('serial', $serial, DatabaseConnection::PARAM_STR);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $core
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectByCore(int $core): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `key` FROM `LockShop_Key_Core` WHERE `core` = ?');
        $select->bindParam(1, $core, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $keys = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$keys[] = self::selectById($id);}
            catch(EntryNotFoundException $e){}
        }

        return $keys;
    }
}