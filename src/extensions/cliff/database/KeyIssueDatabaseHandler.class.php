<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/12/2020
 * Time: 2:19 PM
 */


namespace extensions\cliff\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\cliff\models\KeyIssue;

class KeyIssueDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return KeyIssue
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): KeyIssue
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT * FROM `CLIFF_KeyIssue` WHERE `id` = ? LIMIT 1');
        $s->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\cliff\models\KeyIssue');
    }

    /**
     * @param int $keyId
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectByKey(int $keyId): array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT * FROM `CLIFF_KeyIssue` WHERE `key` = ? ORDER BY `serial`');
        $s->bindParam(1, $keyId, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        return $s->fetchAll(DatabaseConnection::FETCH_CLASS, 'extensions\cliff\models\KeyIssue');
    }

    /**
     * @param int $key
     * @param int $serial
     * @param string $issuedTo
     * @return KeyIssue
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $key, int $serial, string $issuedTo): KeyIssue
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `CLIFF_KeyIssue` (`key`, `serial`, `issuedTo`) VALUES (:key, :serial, :issuedTo)');
        $i->bindParam('key', $key, DatabaseConnection::PARAM_INT);
        $i->bindParam('serial', $serial, DatabaseConnection::PARAM_INT);
        $i->bindParam('issuedTo', $issuedTo, DatabaseConnection::PARAM_STR);
        $i->execute();

        $id = $c->getLastInsertId();

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

        $d = $c->prepare('DELETE FROM `CLIFF_KeyIssue` WHERE `id` = ?');
        $d->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $d->execute();

        $c->close();

        return $d->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param string $issuedTo
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $issuedTo): bool
    {
        $c = new DatabaseConnection();

        $u = $c->prepare('UPDATE `CLIFF_KeyIssue` SET `issuedTo` = ? WHERE `id` = ?');
        $u->bindParam(1, $issuedTo, DatabaseConnection::PARAM_STR);
        $u->bindParam(2, $id, DatabaseConnection::PARAM_INT);
        $u->execute();

        $c->close();

        return $u->getRowCount() === 1;
    }

    /**
     * @param int $keyId
     * @param int $serial
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function serialInUse(int $keyId, int $serial): bool
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `serial` FROM `CLIFF_KeyIssue` WHERE `key` = ? AND `serial` = ? LIMIT 1');
        $s->bindParam(1, $keyId, DatabaseConnection::PARAM_INT);
        $s->bindParam(2, $serial, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        return $s->getRowCount() === 1;
    }
}
