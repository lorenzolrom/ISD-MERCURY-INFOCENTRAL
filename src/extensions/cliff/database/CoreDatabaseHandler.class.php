<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/11/2020
 * Time: 2:17 PM
 */


namespace extensions\cliff\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\cliff\models\Core;

class CoreDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Core
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     */
    public static function selectById(int $id): Core
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `CLIFF_Core`.`id`, `system`, `stamp`, `pinData`, `type`, `keyway`, `notes`, 
            `CLIFF_System`.`code` as `systemCode`, `CLIFF_System`.`name` as `systemName` FROM `CLIFF_Core` INNER JOIN 
            `CLIFF_System` ON `system` = `CLIFF_System`.`id` WHERE `CLIFF_Core`.`id` = ? LIMIT 1');

        $s->bindParam(1, $id, DatabaseConnection::PARAM_INT);

        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\cliff\models\Core');
    }

    /**
     * @param int $system
     * @param string $stamp
     * @return Core
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectBySystemStamp(int $system, string $stamp): Core
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `CLIFF_Core`.`id`, `system`, `stamp`, `pinData`, `type`, `keyway`, `notes`, 
            `CLIFF_System`.`code` as `systemCode`, `CLIFF_System`.`name` as `systemName` FROM `CLIFF_Core` INNER JOIN 
            `CLIFF_System` ON `system` = `CLIFF_System`.`id` WHERE `CLIFF_Core`.`system` = ? AND `CLIFF_Core`.`stamp` = ? LIMIT 1');

        $s->bindParam(1, $system, DatabaseConnection::PARAM_INT);
        $s->bindParam(2, $stamp, DatabaseConnection::PARAM_STR);

        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\cliff\models\Core');
    }

    /**
     * @param string $systemCode
     * @param string $stamp
     * @param string $type
     * @param string $keyway
     * @param string $notes
     * @return Core[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(string $systemCode, string $stamp, string $type, string $keyway, string $notes): array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `CLIFF_Core`.`id`, `system`, `stamp`, `pinData`, `type`, `keyway`, `notes`, 
            `CLIFF_System`.`code` as `systemCode`, `CLIFF_System`.`name` as `systemName` FROM `CLIFF_Core` INNER JOIN 
            `CLIFF_System` ON `system` = `CLIFF_System`.`id` WHERE `stamp` LIKE :stamp AND 
            `type` LIKE :type AND `keyway` LIKE :keyway AND `notes` LIKE :notes AND `system` IN (SELECT `id` FROM 
            `CLIFF_System` WHERE `code` LIKE :systemCode) ORDER BY `systemCode`, `stamp`');

        $s->bindParams(array(
            'systemCode' => $systemCode,
            'stamp' => $stamp,
            'type' => $type,
            'keyway' => $keyway,
            'notes' => $notes
        ));
        $s->execute();

        $c->close();

        return $s->fetchAll(DatabaseConnection::FETCH_CLASS, 'extensions\cliff\models\Core');
    }

    /**
     * @param int $system
     * @param string $stamp
     * @param string $pinData
     * @param string $type
     * @param string $keyway
     * @param string $notes
     * @return Core
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $system, string $stamp, string $pinData, string $type, string $keyway, string $notes): Core
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `CLIFF_Core` (`system`, `stamp`, `pinData`, `type`, `keyway`, `notes`) VALUES (:system, :stamp, :pinData, :type, :keyway, :notes)');
        $i->bindParams(array(
            'system' => $system,
            'stamp' => $stamp,
            'pinData' => $pinData,
            'type' => $type,
            'keyway' => $keyway,
            'notes' => $notes
        ));

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

        $d = $c->prepare('DELETE FROM `CLIFF_Core` WHERE `id` = ?');
        $d->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $d->execute();

        $c->close();

        return $d->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param int $system
     * @param string $stamp
     * @param string $pinData
     * @param string $type
     * @param string $keyway
     * @param string $notes
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, int $system, string $stamp, string $pinData, string $type, string $keyway, string $notes): bool
    {
        $c = new DatabaseConnection();

        $u = $c->prepare('UPDATE `CLIFF_Core` SET `system` = :system, `stamp` = :stamp, `pinData` = :pinData, `type` = :type, `keyway` = :keyway, `notes` = :notes WHERE `id` = :id');
        $u->bindParams(array(
            'id' => $id,
            'system' => $system,
            'stamp' => $stamp,
            'pinData' => $pinData,
            'type' => $type,
            'keyway' => $keyway,
            'notes' => $notes
        ));
        $u->execute();

        $c->close();

        return $u->getRowCount() === 1;
    }

    /**
     * Checks if the system/stamp combination is already in the database.  These two values form a unique key.
     * @param int $system
     * @param string $stamp
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function stampInUse(int $system, string $stamp): bool
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `stamp` FROM `CLIFF_Core` WHERE `system` = :system AND `stamp` = :stamp LIMIT 1');
        $s->bindParams(array(
            'system' => $system,
            'stamp' => $stamp
        ));
        $s->execute();

        $c->close();

        return $s->getRowCount() === 1;
    }
}