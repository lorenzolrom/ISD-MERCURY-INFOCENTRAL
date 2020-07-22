<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/25/2019
 * Time: 11:05 AM
 */


namespace extensions\itsm\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\itsm\models\Vendor;

class VendorDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Vendor
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     */
    public static function selectById(int $id): Vendor
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id`, `code`, `name`, `streetAddress`, `city`, `state`, `zipCode`,
            `phone`, `fax` FROM `ITSM_Vendor`
            WHERE `id` = ? LIMIT 1");

        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("extensions\itsm\models\Vendor");
    }

    /**
     * @param string $code
     * @param string $name
     * @param string $streetAddress
     * @param string $city
     * @param string $state
     * @param string $zipCode
     * @param string $phone
     * @param string $fax
     * @return Vendor[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $code = '%', string $name = '%', string $streetAddress = '%',
                                  string $city = '%', string $state = '%', string $zipCode = '%', string $phone = '%',
                                  string $fax = '%'): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `ITSM_Vendor` WHERE `code` LIKE :code AND `name` LIKE :name AND 
                                     `streetAddress` LIKE :streetAddress AND `city` LIKE :city AND `state` LIKE :state AND 
                                     `zipCode` LIKE :zipCode AND `phone` LIKE :phone AND `fax` LIKE :fax ORDER BY `code` ASC");

        $select->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $select->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $select->bindParam('streetAddress', $streetAddress, DatabaseConnection::PARAM_STR);
        $select->bindParam('city', $city, DatabaseConnection::PARAM_STR);
        $select->bindParam('state', $state, DatabaseConnection::PARAM_STR);
        $select->bindParam('zipCode', $zipCode, DatabaseConnection::PARAM_STR);
        $select->bindParam('phone', $phone, DatabaseConnection::PARAM_STR);
        $select->bindParam('fax', $fax, DatabaseConnection::PARAM_STR);

        $select->execute();

        $handler->close();

        $vendors = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $vendors[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $vendors;
    }

    /**
     * @param string $code
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectIdFromCode(string $code): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `ITSM_Vendor` WHERE `code` = ? LIMIT 1');
        $select->bindParam(1, $code, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            return NULL;

        return $select->fetchColumn();
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare("DELETE FROM `ITSM_Vendor` WHERE `id` = ?");
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param string $code
     * @param string $name
     * @param string $streetAddress
     * @param string $city
     * @param string $state
     * @param string $zipCode
     * @param string $phone
     * @param string $fax
     * @return Vendor
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $code, string $name, string $streetAddress, string $city,
                                  string $state, string $zipCode, string $phone, string $fax): Vendor
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `ITSM_Vendor` SET `code` = :code, `name` = :name, 
                         `streetAddress` = :streetAddress, `city` = :city, `state` = :state, `zipCode` = :zipCode, 
                         `phone` = :phone, `fax` = :fax WHERE `id` = :id');

        $update->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $update->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $update->bindParam('streetAddress', $streetAddress, DatabaseConnection::PARAM_STR);
        $update->bindParam('city', $city, DatabaseConnection::PARAM_STR);
        $update->bindParam('state', $state, DatabaseConnection::PARAM_STR);
        $update->bindParam('zipCode', $zipCode, DatabaseConnection::PARAM_STR);
        $update->bindParam('phone', $phone, DatabaseConnection::PARAM_STR);
        $update->bindParam('fax', $fax, DatabaseConnection::PARAM_STR);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
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
     * @param string $phone
     * @param string $fax
     * @return Vendor
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     */
    public static function insert(string $code, string $name, string $streetAddress, string $city, string $state,
                                  string $zipCode, string $phone, string $fax): Vendor
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `ITSM_Vendor` (code, name, streetAddress, city, state, zipCode, 
                           phone, fax) VALUES (:code, :name, 
                           :streetAddress, :city, :state, :zipCode, :phone, :fax)');

        $insert->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $insert->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $insert->bindParam('streetAddress', $streetAddress, DatabaseConnection::PARAM_STR);
        $insert->bindParam('city', $city, DatabaseConnection::PARAM_STR);
        $insert->bindParam('state', $state, DatabaseConnection::PARAM_STR);
        $insert->bindParam('zipCode', $zipCode, DatabaseConnection::PARAM_STR);
        $insert->bindParam('phone', $phone, DatabaseConnection::PARAM_STR);
        $insert->bindParam('fax', $fax, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }
}
