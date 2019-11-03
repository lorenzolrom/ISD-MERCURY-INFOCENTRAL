<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/03/2019
 * Time: 10:21 AM
 */


namespace extensions\facilities\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\facilities\models\Floorplan;

class FloorplanDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $building
     * @param string $floor
     * @return Floorplan
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function select(int $building, string $floor): Floorplan
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `building`, `floor`, `imageType`, `imageName` FROM `Facilities_Floorplan` WHERE `building` = :building AND `floor` = :floor LIMIT 1');
        $s->bindParam('building', $building, DatabaseConnection::PARAM_INT);
        $s->bindParam('floor', $floor, DatabaseConnection::PARAM_STR);
        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\facilities\models\Floorplan');
    }

    /**
     * @param int $building
     * @param string $floor
     * @param string $imageType
     * @param string $imageName
     * @return Floorplan
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $building, string $floor, string $imageType, string $imageName): Floorplan
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `Facilities_Floorplan`(`building`, `floor`, `imageType`, `imageName`) VALUES (:building, :floor, :imageType, :imageName)');
        $i->bindParam('building', $building, DatabaseConnection::PARAM_INT);
        $i->bindParam('floor', $floor, DatabaseConnection::PARAM_STR);
        $i->bindParam('imageName', $imageName, DatabaseConnection::PARAM_STR);
        $i->bindParam('imageType', $imageType, DatabaseConnection::PARAM_STR);
        $i->execute();

        $c->close();

        return self::select($building, $floor);
    }

    /**
     * @param int $building
     * @param string $floor
     * @param string $newFloor
     * @param string $imageType
     * @param string $imageName
     * @return Floorplan
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $building, string $floor, string $newFloor, string $imageType, string $imageName): Floorplan
    {
        $c = new DatabaseConnection();

        $u = $c->prepare('UPDATE `Facilities_Floorplan` SET `floor` = :newFloor, `imageType` = :imageType, `imageName` = :imageName WHERE `building` = :building AND `floor` = :floor');
        $u->bindParam('building', $building, DatabaseConnection::PARAM_INT);
        $u->bindParam('floor', $floor, DatabaseConnection::PARAM_STR);
        $u->bindParam('newFloor', $newFloor, DatabaseConnection::PARAM_STR);
        $u->bindParam('imageName', $imageName, DatabaseConnection::PARAM_STR);
        $u->bindParam('imageType', $imageType, DatabaseConnection::PARAM_STR);
        $u->execute();

        $c->close();

        return self::select($building, $floor);
    }

    /**
     * @param int $building
     * @param string $floor
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $building, string $floor): bool
    {
        $c = new DatabaseConnection();

        $d = $c->prepare('DELETE FROM `Facilities_Floorplan` WHERE `building` = :building AND `floor` = :floor');
        $d->bindParam('building', $building, DatabaseConnection::PARAM_INT);
        $d->bindParam('floor', $floor, DatabaseConnection::PARAM_STR);
        $d->execute();

        $c->close();

        return $d->getRowCount() === 1;
    }
}