<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/25/2019
 * Time: 11:05 AM
 */


namespace database\itsm;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use models\itsm\Vendor;

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
            `phone`, `fax`, `createDate`, `createUser`, `lastModifyDate`, `lastModifyUser` FROM `ITSM_Vendor`
            WHERE `id` = ? LIMIT 1");

        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("models\itsm\Vendor");
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
}