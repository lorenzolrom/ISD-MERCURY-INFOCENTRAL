<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/02/2020
 * Time: 7:34 PM
 */


namespace extensions\cliff\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\cliff\models\System;

class SystemDatabaseHandler extends DatabaseHandler
{
    /**
     * @param string $code
     * @param string $name
     * @return System[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(string $code, string $name): array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT * FROM `CLIFF_System` WHERE `code` LIKE :code AND `name` LIKE :name ORDER BY `code` ASC');
        $s->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $s->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $s->execute();

        $c->close();

        return $s->fetchAll(DatabaseConnection::FETCH_CLASS, 'extensions\cliff\models\System');
    }

    /**
     * @param string $code
     * @return System
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectByCode(string $code): System
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT * FROM `CLIFF_System` WHERE `code` = :code LIMIT 1');
        $s->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\cliff\models\System');
    }

    /**
     * @param int $id
     * @return System
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): System
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT * FROM `CLIFF_System` WHERE `id` = :id LIMIT 1');
        $s->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\cliff\models\System');
    }

    /**
     * @param string $code
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function isCodeInUse(string $code): bool
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `code` FROM `CLIFF_System` WHERE `code` = :code LIMIT 1');
        $s->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $s->execute();

        $c->close();

        return $s->getRowCount() === 1;
    }

    /**
     * @param string $code
     * @param string $name
     * @return System
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $code, string $name): System
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `CLIFF_System` (`code`, `name`) VALUES (:code, :name)');
        $i->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $i->bindParam('name', $name, DatabaseConnection::PARAM_STR);
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

        $d = $c->prepare('DELETE FROM `CLIFF_System` WHERE `id` = :id');
        $d->bindParam('id', $id, DatabaseConnection::PARAM_STR);
        $d->execute();

        $c->close();

        return $d->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param string $code
     * @param string $name
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $code, string $name): bool
    {
        $c = new DatabaseConnection();

        $u = $c->prepare('UPDATE `CLIFF_System` SET `code` = :code, `name` = :name WHERE `id` = :id');
        $u->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $u->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $u->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $u->execute();

        $c->close();

        return $u->getRowCount() === 1;
    }
}
