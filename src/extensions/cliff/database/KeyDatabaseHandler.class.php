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
use extensions\cliff\models\Key;

class KeyDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Key
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     */
    public static function selectById(int $id): Key
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `CLIFF_Key`.`id`, `system`, `stamp`, `bitting`, `type`, `keyway`, `notes`, 
            `CLIFF_System`.`code` as `systemCode`, `CLIFF_System`.`name` as `systemName` FROM `CLIFF_Key` INNER JOIN 
            `CLIFF_System` ON `system` = `CLIFF_System`.`id` WHERE `CLIFF_Key`.`id` = ? LIMIT 1');

        $s->bindParam(1, $id, DatabaseConnection::PARAM_INT);

        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\cliff\models\Key');
    }

    /**
     * @param int $systemID
     * @param string $stamp
     * @return Key
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectBySystemStamp(int $systemID, string $stamp): Key
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `CLIFF_Key`.`id`, `system`, `stamp`, `bitting`, `type`, `keyway`, `notes`, 
            `CLIFF_System`.`code` as `systemCode`, `CLIFF_System`.`name` as `systemName` FROM `CLIFF_Key` INNER JOIN 
            `CLIFF_System` ON `system` = `CLIFF_System`.`id` WHERE `CLIFF_Key`.`system` = ? AND `CLIFF_Key`.`stamp` = ? LIMIT 1');

        $s->bindParam(1, $systemID, DatabaseConnection::PARAM_INT);
        $s->bindParam(2, $stamp, DatabaseConnection::PARAM_STR);

        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\cliff\models\Key');
    }

    /**
     * @param string $systemCode
     * @param string $stamp
     * @param string $bitting
     * @param string $type
     * @param string $keyway
     * @param string $notes
     * @return Key[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(string $systemCode, string $stamp, string $bitting, string $type, string $keyway, string $notes): array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `CLIFF_Key`.`id`, `system`, `stamp`, `bitting`, `type`, `keyway`, `notes`, 
            `CLIFF_System`.`code` as `systemCode`, `CLIFF_System`.`name` as `systemName` FROM `CLIFF_Key` INNER JOIN 
            `CLIFF_System` ON `system` = `CLIFF_System`.`id` WHERE `stamp` LIKE :stamp AND `bitting` LIKE :bitting AND 
            `type` LIKE :type AND `keyway` LIKE :keyway AND `notes` LIKE :notes AND `system` IN (SELECT `id` FROM 
            `CLIFF_System` WHERE `code` LIKE :systemCode) ORDER BY `systemCode`, `stamp`');

        $s->bindParams(array(
            'systemCode' => $systemCode,
            'stamp' => $stamp,
            'bitting' => $bitting,
            'type' => $type,
            'keyway' => $keyway,
            'notes' => $notes
        ));
        $s->execute();

        $c->close();

        return $s->fetchAll(DatabaseConnection::FETCH_CLASS, 'extensions\cliff\models\Key');
    }

    /**
     * @param int $system
     * @param string $stamp
     * @param string $bitting
     * @param string $type
     * @param string $keyway
     * @param string $notes
     * @return Key
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $system, string $stamp, string $bitting, string $type, string $keyway, string $notes): Key
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `CLIFF_Key` (`system`, `stamp`, `bitting`, `type`, `keyway`, `notes`) VALUES (:system, :stamp, :bitting, :type, :keyway, :notes)');
        $i->bindParams(array(
            'system' => $system,
            'stamp' => $stamp,
            'bitting' => $bitting,
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

        $d = $c->prepare('DELETE FROM `CLIFF_Key` WHERE `id` = ?');
        $d->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $d->execute();

        $c->close();

        return $d->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param int $system
     * @param string $stamp
     * @param string $bitting
     * @param string $type
     * @param string $keyway
     * @param string $notes
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, int $system, string $stamp, string $bitting, string $type, string $keyway, string $notes): bool
    {
        $c = new DatabaseConnection();

        $u = $c->prepare('UPDATE `CLIFF_Key` SET `system` = :system, `stamp` = :stamp, `bitting` = :bitting, `type` = :type, `keyway` = :keyway, `notes` = :notes WHERE `id` = :id');
        $u->bindParams(array(
            'id' => $id,
            'system' => $system,
            'stamp' => $stamp,
            'bitting' => $bitting,
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

        $s = $c->prepare('SELECT `stamp` FROM `CLIFF_Key` WHERE `system` = :system AND `stamp` = :stamp LIMIT 1');
        $s->bindParams(array(
            'system' => $system,
            'stamp' => $stamp
        ));
        $s->execute();

        $c->close();

        return $s->getRowCount() === 1;
    }

    /**
     * Select the ID of a key in the specified system with matching bitting, if it exists
     * @param int $system
     * @param string $bitting
     * @return array|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectIDBySystemBitting(int $system, string $bitting): ?array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `stamp` FROM `CLIFF_Key` WHERE `system` = ? AND `bitting` = ? LIMIT 1');
        $s->bindParam(1, $system, DatabaseConnection::PARAM_INT);
        $s->bindParam(2, $bitting, DatabaseConnection::PARAM_STR);
        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            return NULL;

        return $s->fetch();
    }

    /**
     * @param string $issuedTo
     * @return Key[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectByIssuedTo(string $issuedTo): array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `CLIFF_Key`.*, `CLIFF_System`.`code` as `systemCode`, `CLIFF_System`.`name` as `systemName`
                FROM `CLIFF_Key` INNER JOIN `CLIFF_System` ON `system` = `CLIFF_System`.`id`
                WHERE `CLIFF_Key`.`id` IN (SELECT `key` FROM `CLIFF_KeyIssue` WHERE `issuedTo` LIKE :issuedTo)
                ORDER BY `systemCode`, `stamp`');
        $s->bindParam('issuedTo', $issuedTo, DatabaseConnection::PARAM_STR);
        $s->execute();

        $c->close();

        return $s->fetchAll(DatabaseConnection::FETCH_CLASS, 'extensions\cliff\models\Key');
    }
}