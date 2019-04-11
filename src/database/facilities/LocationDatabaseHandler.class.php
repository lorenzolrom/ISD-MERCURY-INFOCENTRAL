<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/09/2019
 * Time: 8:58 PM
 */


namespace database\facilities;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\DatabaseException;
use exceptions\EntryInUseException;
use exceptions\EntryNotFoundException;
use models\facilities\Location;

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

        $select = $handler->prepare("SELECT id, building, code, name, createDate, createUser, lastModifyDate, lastModifyUser FROM FacilitiesCore_Location WHERE id = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 0)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('models\facilities\Location');
    }

    /**
     * @param int $buildingId
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectByBuilding(int $buildingId): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT id FROM FacilitiesCore_Location WHERE building = ?");
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
     * @param int $building
     * @param string $code
     * @param string $name
     * @param string $createDate
     * @param int $createUser
     * @param string $lastModifyDate
     * @param int $lastModifyUser
     * @return Location
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function create(int $building, string $code, string $name, string $createDate, int $createUser, string $lastModifyDate, int $lastModifyUser): Location
    {
        $handler = new DatabaseConnection();

        $create = $handler->prepare("INSERT INTO FacilitiesCore_Location (building, code, name, createDate, 
                                     createUser, lastModifyDate, lastModifyUser) VALUES (:building, :code, :name, 
                                     :createDate, :createUser, :lastModifyDate, :lastModifyUser)");

        $create->bindParam('building', $building, DatabaseConnection::PARAM_INT);
        $create->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $create->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $create->bindParam('createDate', $createDate, DatabaseConnection::PARAM_STR);
        $create->bindParam('createUser', $createUser, DatabaseConnection::PARAM_INT);
        $create->bindParam('lastModifyDate', $lastModifyDate, DatabaseConnection::PARAM_STR);
        $create->bindParam('lastModifyUser', $lastModifyUser, DatabaseConnection::PARAM_INT);
        $create->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $code
     * @param string $name
     * @param string $lastModifyDate
     * @param int $lastModifyUser
     * @return Location
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $code, string $name, string $lastModifyDate, int $lastModifyUser): Location
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare("UPDATE FacilitiesCore_Location SET code = :code, name = :name, lastModifyDate = :lastModifyDate, lastModifyUser = :lastModifyUser WHERE id = :id");
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $update->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $update->bindParam('lastModifyDate', $lastModifyDate, DatabaseConnection::PARAM_STR);
        $update->bindParam('lastModifyUser', $lastModifyUser, DatabaseConnection::PARAM_INT);
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
            $delete = $handler->prepare("DELETE FROM FacilitiesCore_Location WHERE id = ?");
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
            $delete = $handler->prepare("DELETE FROM FacilitiesCore_Location WHERE building = ?");
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

        $select = $handler->prepare("SELECT id FROM FacilitiesCore_Location WHERE building = :building AND code = :code LIMIT 1");
        $select->bindParam('building', $buildingId, DatabaseConnection::PARAM_INT);
        $select->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }
}