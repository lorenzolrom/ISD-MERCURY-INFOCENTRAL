<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/09/2019
 * Time: 8:58 PM
 */


namespace extensions\facilities\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\DatabaseException;
use exceptions\EntryInUseException;
use exceptions\EntryNotFoundException;
use extensions\facilities\models\Location;

class LocationDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Location
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Location
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id`, `building`, `code`, `name` FROM `FacilitiesCore_Location` WHERE `id` = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 0)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('extensions\facilities\models\Location');
    }

    /**
     * @param int $buildingId
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectByBuilding(int $buildingId): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `FacilitiesCore_Location` WHERE `building` = ?");
        $select->bindParam(1, $buildingId, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $locations = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $locations[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $locations;
    }

    /**
     * @param string $buildingCode
     * @param string $locationCode
     * @return Location
     * @throws DatabaseException
     * @throws EntryNotFoundException
     */
    public static function selectByCode(string $buildingCode, string $locationCode): Location
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `FacilitiesCore_Location` WHERE `code` = :locationCode AND `building` IN (SELECT `id` FROM `FacilitiesCore_Building` WHERE FacilitiesCore_Building.`code` = :buildingCode) LIMIT 1');
        $select->bindParam('locationCode', $locationCode, DatabaseConnection::PARAM_STR);
        $select->bindParam('buildingCode', $buildingCode, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 0)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        return self::selectById($select->fetchColumn());
    }

    /**
     * @param int $building
     * @param string $code
     * @param string $name
     * @return Location
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function create(int $building, string $code, string $name): Location
    {
        $handler = new DatabaseConnection();

        $create = $handler->prepare("INSERT INTO `FacilitiesCore_Location` (`building`, `code`, `name`) VALUES (:building, :code, :name)");

        $create->bindParam('building', $building, DatabaseConnection::PARAM_INT);
        $create->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $create->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $create->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $code
     * @param string $name
     * @return Location
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $code, string $name): Location
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare("UPDATE `FacilitiesCore_Location` SET `code` = :code, `name` = :name WHERE `id` = :id");
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $update->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws EntryInUseException
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        try
        {
            $delete = $handler->prepare("DELETE FROM `FacilitiesCore_Location` WHERE `id` = ?");
            $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
            $delete->execute();
        }
        catch(DatabaseException $e)
        {
            throw new EntryInUseException(EntryInUseException::MESSAGES[EntryInUseException::ENTRY_IN_USE], EntryInUseException::ENTRY_IN_USE, $e);
        }

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $buildingId
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws EntryInUseException
     */
    public static function deleteByBuilding(int $buildingId): bool
    {
        $handler = new DatabaseConnection();

        try
        {
            $delete = $handler->prepare("DELETE FROM `FacilitiesCore_Location` WHERE `building` = ?");
            $delete->bindParam(1, $buildingId, DatabaseConnection::PARAM_INT);
            $delete->execute();
        }
        catch(DatabaseException $e)
        {
            throw new EntryInUseException(EntryInUseException::MESSAGES[EntryInUseException::ENTRY_IN_USE], EntryInUseException::ENTRY_IN_USE, $e);
        }

        $handler->close();

        return $delete->getRowCount() !== 0;
    }

    /**
     * @param int $buildingId
     * @param string $code
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function isCodeInUse(int $buildingId, string $code): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT id FROM `FacilitiesCore_Location` WHERE `building` = :building AND `code` = :code LIMIT 1");
        $select->bindParam('building', $buildingId, DatabaseConnection::PARAM_INT);
        $select->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $code
     * @return string|null
     * @throws DatabaseException
     */
    public static function selectCodeFromId(int $code): ?string
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `code` FROM `FacilitiesCore_Location` WHERE `id` = ? LIMIT 1");
        $select->bindParam(1, $code, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            return null;

        return $select->fetchColumn();
    }

    /**
     * @param int $id
     * @return int|null
     * @throws DatabaseException
     */
    public static function selectBuildingFromId(int $id): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `building` FROM `FacilitiesCore_Location` WHERE `id` = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            return null;

        return $select->fetchColumn();
    }
}
