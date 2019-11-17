<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/17/2019
 * Time: 12:34 PM
 */


namespace extensions\faclocks\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\faclocks\models\Lock;

class LockDatabaseHandler extends DatabaseHandler
{

    /**
     * @param int $id
     * @return Lock
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Lock
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `system`, `code` FROM `FacilitiesLock_Lock` WHERE `id` = ? LIMIT 1');
        $s->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\faclocks\models\Lock');
    }

    /**
     * @param int $system
     * @param string $code
     * @return Lock
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectBySystemCode(int $system, string $code): Lock
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `system`, `code` FROM `FacilitiesLock_Lock` WHERE `system` = :system AND `code` = :code LIMIT 1');
        $s->bindParam('system', $system, DatabaseConnection::PARAM_INT);
        $s->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\faclocks\models\Lock');
    }

    /**
     * @param int $system
     * @param string $code
     * @return Lock
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $system, string $code): Lock
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `FacilitiesLock_Lock`(`system`, `code`) VALUES (:system, :code)');
        $i->bindParam('system', $system, DatabaseConnection::PARAM_INT);
        $i->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $i->execute();

        $id = $c->getLastInsertId();

        $c->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $code
     * @return Lock
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $code): Lock
    {
        $c = new DatabaseConnection();

        $u = $c->prepare('UPDATE `FacilitiesLock_Lock` SET `code` = :code WHERE `id` = :id');
        $u->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $u->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $u->execute();

        $c->close();
        return self::selectById($id);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $c = new DatabaseConnection();

        $d = $c->prepare('DELETE FROM `FacilitiesLock_Lock` WHERE `id` = ?');
        $d->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $d->execute();

        $c->close();

        return $d->getRowCount() === 1;
    }
}