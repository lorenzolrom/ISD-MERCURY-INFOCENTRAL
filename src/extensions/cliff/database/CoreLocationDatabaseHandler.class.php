<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
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
use extensions\cliff\models\CoreLocation;

class CoreLocationDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return CoreLocation
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): CoreLocation
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT * FROM `CLIFF_CoreLocation` WHERE `id` = ? LIMIT 1');
        $s->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\cliff\models\CoreLocation');
    }

    /**
     * @param int $coreId
     * @return CoreLocation[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectByCore(int $coreId): array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT * FROM `CLIFF_CoreLocation` WHERE `core` = ? ORDER BY `building`, `location`');
        $s->bindParam(1, $coreId, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        return $s->fetchAll(DatabaseConnection::FETCH_CLASS, 'extensions\cliff\models\CoreLocation');
    }

    /**
     * @param int $core
     * @param string $building
     * @param string $location
     * @param string $notes
     * @return CoreLocation
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $core, string $building, string $location, string $notes): CoreLocation
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `CLIFF_CoreLocation` (`core`, `building`, `location`, `notes`) VALUES (:core, :building, :location, :notes)');
        $i->bindParam('core', $core, DatabaseConnection::PARAM_INT);
        $i->bindParam('building', $building, DatabaseConnection::PARAM_STR);
        $i->bindParam('location', $location, DatabaseConnection::PARAM_STR);
        $i->bindParam('notes', $notes, DatabaseConnection::PARAM_STR);
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

        $d = $c->prepare('DELETE FROM `CLIFF_CoreLocation` WHERE `id` = ?');
        $d->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $d->execute();

        $c->close();

        return $d->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param string $building
     * @param string $location
     * @param string $notes
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $building, string $location, string $notes): bool
    {
        $c = new DatabaseConnection();

        $u = $c->prepare('UPDATE `CLIFF_CoreLocation` SET `building` = ?, `location` = ?, `notes` = ? WHERE `id` = ?');
        $u->bindParam(1, $building, DatabaseConnection::PARAM_STR);
        $u->bindParam(2, $location, DatabaseConnection::PARAM_STR);
        $u->bindParam(3, $notes, DatabaseConnection::PARAM_STR);
        $u->bindParam(4, $id, DatabaseConnection::PARAM_INT);
        $u->execute();

        $c->close();

        return $u->getRowCount() === 1;
    }
}
