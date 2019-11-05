<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/04/2019
 * Time: 10:34 AM
 */


namespace extensions\facilities\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\facilities\models\Space;

class SpaceDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $location
     * @return Space
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function select(int $location): Space
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `location`, `floor`, `hexColor`, `area`, `unit` FROM `Facilities_Space` WHERE `location` = ? LIMIT 1');
        $s->bindParam(1, $location, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\facilities\models\Space');
    }

    /**
     * @param int $floor
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectByFloor(int $floor): array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `location`, `floor`, `hexColor`, `area`, `unit` FROM `Facilities_Space` WHERE `floor` = ?');
        $s->bindParam(1, $floor, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        return $s->fetchAll(DatabaseConnection::FETCH_CLASS, 'extensions\facilities\models\Space');
    }

    /**
     * @param int $location
     * @param int $floor
     * @param string $hexColor
     * @param float $area
     * @param string $unit
     * @return Space
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $location, int $floor, string $hexColor, float $area, string $unit): Space
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `Facilities_Space` (`location`, `floor`, `hexColor`, `area`, `unit`) VALUES (:location, :floor, :hexColor, :area, :unit)');
        $i->bindParam('location', $location, DatabaseConnection::PARAM_INT);
        $i->bindParam('floor', $floor, DatabaseConnection::PARAM_INT);
        $i->bindParam('hexColor', $hexColor, DatabaseConnection::PARAM_STR);
        $i->bindParam('area', $area, DatabaseConnection::PARAM_STR);
        $i->bindParam('unit', $unit, DatabaseConnection::PARAM_STR);
        $i->execute();

        $c->close();

        return self::select($location);
    }

    /**
     * @param int $location
     * @param string $hexColor
     * @param float $area
     * @param string $unit
     * @return Space
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $location, string $hexColor, float $area, string $unit): Space
    {
        $c = new DatabaseConnection();

        $u = $c->prepare('UPDATE `Facilities_Space` SET `hexColor` = :hexColor, `area` = :area, `unit` = :unit WHERE `location` = :location');
        $u->bindParam('location', $location, DatabaseConnection::PARAM_INT);
        $u->bindParam('hexColor', $hexColor, DatabaseConnection::PARAM_STR);
        $u->bindParam('area', $area, DatabaseConnection::PARAM_STR);
        $u->bindParam('unit', $unit, DatabaseConnection::PARAM_STR);
        $u->execute();

        $c->close();

        return self::select($location);
    }

    /**
     * @param int $location
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $location): bool
    {
        $c = new DatabaseConnection();

        $d = $c->prepare('DELETE FROM `Facilities_Space` WHERE `location` = ?');
        $d->bindParam(1, $location, DatabaseConnection::PARAM_INT);
        $d->execute();

        $c->close();

        return $d->getRowCount() === 1;
    }
}