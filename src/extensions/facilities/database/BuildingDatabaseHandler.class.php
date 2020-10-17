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
use exceptions\EntryNotFoundException;
use extensions\facilities\models\Building;

class BuildingDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Building
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     */
    public static function selectById(int $id):Building
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT id, code, name, streetAddress, city, state, zipCode FROM FacilitiesCore_Building WHERE id = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('extensions\facilities\models\Building');
    }

    /**
     * @param string $code
     * @param string $name
     * @param string $streetAddress
     * @param string $city
     * @param string $state
     * @param string $zipCode
     * @return Building[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $code = '%', string $name ='%', string $streetAddress = '%', string $city = '%', string $state = '%', string $zipCode = '%'): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT id FROM FacilitiesCore_Building WHERE code LIKE :code AND name LIKE :name AND streetAddress LIKE :streetAddress 
                                         AND city LIKE :city AND state LIKE :state AND zipCode LIKE :zipCode ORDER BY code ASC");

        $select->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $select->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $select->bindParam('streetAddress', $streetAddress, DatabaseConnection::PARAM_STR);
        $select->bindParam('city', $city, DatabaseConnection::PARAM_STR);
        $select->bindParam('state', $state, DatabaseConnection::PARAM_STR);
        $select->bindParam('zipCode', $zipCode, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $buildings = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $buildings[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $buildings;
    }

    /**
     * @param int $id
     * @param string $code
     * @param string $name
     * @param string $streetAddress
     * @param string $city
     * @param string $state
     * @param string $zipCode
     * @return Building
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $code, string $name, string $streetAddress, string $city,
                                  string $state, string $zipCode): Building
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare("UPDATE FacilitiesCore_Building SET code = :code, name = :name, 
                                   streetAddress = :streetAddress, city = :city, state = :state, zipCode = :zipCode 
                                   WHERE id = :id");

        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $update->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $update->bindParam('streetAddress', $streetAddress, DatabaseConnection::PARAM_STR);
        $update->bindParam('city', $city, DatabaseConnection::PARAM_STR);
        $update->bindParam('state', $state, DatabaseConnection::PARAM_STR);
        $update->bindParam('zipCode', $zipCode, DatabaseConnection::PARAM_STR);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param string $code
     * @param string $name
     * @param string $streetAddress
     * @param string $city
     * @param string $state
     * @param string $zipCode
     * @return Building
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function create(string $code, string $name, string $streetAddress, string $city, string $state,
                                  string $zipCode): Building
    {
        $handler = new DatabaseConnection();

        $create = $handler->prepare("INSERT INTO FacilitiesCore_Building (code, name, streetAddress, city, state, 
                                     zipCode) VALUES (:code, 
                                      :name, :streetAddress, :city, :state, :zipCode)");

        $create->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $create->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $create->bindParam('streetAddress', $streetAddress, DatabaseConnection::PARAM_STR);
        $create->bindParam('city', $city, DatabaseConnection::PARAM_STR);
        $create->bindParam('state', $state, DatabaseConnection::PARAM_STR);
        $create->bindParam('zipCode', $zipCode, DatabaseConnection::PARAM_STR);
        $create->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare("DELETE FROM FacilitiesCore_Building WHERE id = ?");
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param string $code
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function codeInUse(string $code): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT id FROM FacilitiesCore_Building WHERE code = ? LIMIT 1");
        $select->bindParam(1, $code, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $code
     * @return string|null
     * @throws DatabaseException
     * @throws \exceptions\DatabaseException
     */
    public static function selectCodeFromId(int $code): ?string
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `code` FROM `FacilitiesCore_Building` WHERE `id` = ? LIMIT 1");
        $select->bindParam(1, $code, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            return null;

        return $select->fetchColumn();
    }

    /**
     * @param string $code
     * @return int|null
     * @throws DatabaseException
     */
    public static function selectIdFromCode(string $code): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `FacilitiesCore_Building` WHERE `code` = ? LIMIT 1");
        $select->bindParam(1, $code, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            return null;

        return $select->fetchColumn();
    }
}
