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
    public static function select(int $id): System
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `code`, `master`, `control`, `parent` FROM `FacilitiesLock_System` WHERE `id` = ? LIMIT 1');
        $s->bindParam(1, $id, DatabaseConnection::PARAM_INT);

        $c->close();
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

        return self::select($id);
    }
}