<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
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
    public static function selectByBuildingFloor(int $building, string $floor): Floorplan
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
     * @param int $id
     * @return Floorplan
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Floorplan
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `building`, `floor`, `imageType`, `imageName` FROM `Facilities_Floorplan` WHERE `id` = :id LIMIT 1');
        $s->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\facilities\models\Floorplan');
    }

    /**
     * @param string $buildingCodeFilter
     * @param string $floorFilter
     * @return Floorplan[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $buildingCodeFilter, string $floorFilter): array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id` FROM `Facilities_Floorplan` WHERE `building` IN (SELECT `id` FROM `FacilitiesCore_Building` WHERE `code` LIKE :buildingCode) AND `floor` LIKE :floor');
        $s->bindParam('buildingCode', $buildingCodeFilter, DatabaseConnection::PARAM_STR);
        $s->bindParam('floor', $floorFilter, DatabaseConnection::PARAM_STR);
        $s->execute();

        $c->close();

        $floorplans = array();

        foreach($s->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{
                $floorplans[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){} // Do nothing
        }

        return $floorplans;
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

        return self::selectByBuildingFloor($building, $floor);
    }

    /**
     * @param int $id
     * @param int $building
     * @param string $floor
     * @param string $newFloor
     * @param string $imageType
     * @param string $imageName
     * @return Floorplan
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, int $building, string $floor, string $newFloor, string $imageType, string $imageName): Floorplan
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

        return self::selectById($id);
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
