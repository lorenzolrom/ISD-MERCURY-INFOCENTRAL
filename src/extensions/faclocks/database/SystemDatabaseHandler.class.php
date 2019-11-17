<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/08/2019
 * Time: 11:07 AM
 */


namespace extensions\faclocks\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use extensions\faclocks\models\System;

class SystemDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return System
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): System
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `code`, `master`, `control`, `parent` FROM `FacilitiesLock_System` WHERE `id` = ? LIMIT 1');
        $s->bindParam(1, $id, DatabaseConnection::PARAM_INT);

        $c->close();
        return $s->fetchObject('extensions\faclocks\models\System');
    }

    /**
     * @param string $code
     * @return System
     * @throws \exceptions\DatabaseException
     */
    public static function selectByCode(string $code): System
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `code`, `master`, `control`, `parent` FROM `FacilitiesLock_System` WHERE `code` = ? LIMIT 1');
        $s->bindParam(1, $code, DatabaseConnection::PARAM_STR);

        $c->close();
        return $s->fetchObject('extensions\faclocks\models\System');
    }

    /**
     * @param string $code
     * @return System
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $code): System
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `FacilitiesLock_System`(`code`) VALUES (:code)');
        $i->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $i->execute();

        $id = $c->getLastInsertId();

        $c->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $code
     * @param int|null $master
     * @param int|null $control
     * @param int|null $parent
     * @return System
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $code, ?int $master, ?int $control, ?int $parent): System
    {
        $c = new DatabaseConnection();

        $u = $c->prepare('UPDATE `FacilitiesLock_System` SET `code` = :code, `master` = :master, 
                                   `control` = :control, `parent` = :parent WHERE `id` = :id');
        $u->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $u->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $u->bindParam('master', $master, DatabaseConnection::PARAM_INT);
        $u->bindParam('control', $control, DatabaseConnection::PARAM_INT);
        $u->bindParam('parent', $parent, DatabaseConnection::PARAM_INT);
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

        $d = $c->prepare('DELETE FROM `FacilitiesLock_System` WHERE `id` = ?');
        $d->bindParam(1, $id, DatabaseConnection::PARAM_INT);

        $c->close();

        return $d->getRowCount() === 1;
    }
}