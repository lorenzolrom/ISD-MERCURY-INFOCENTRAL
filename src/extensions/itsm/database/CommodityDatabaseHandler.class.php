<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/12/2019
 * Time: 2:57 PM
 */


namespace extensions\itsm\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\itsm\models\Commodity;

class CommodityDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Commodity
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     */
    public static function selectById(int $id): Commodity
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id`, `code`, `name`, `commodityType`, `assetType`, `manufacturer`, 
                                           `model`, `unitCost` FROM `ITSM_Commodity` WHERE `id` = ? LIMIT 1");

        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("extensions\itsm\models\Commodity");
    }

    /**
     * @param string $code
     * @return Commodity
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectByCode(string $code): Commodity
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `ITSM_Commodity` WHERE `code` = ? LIMIT 1');
        $select->bindParam(1, $code, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        return self::selectById($select->fetchColumn());
    }

    /**
     * @param string $code
     * @param string $name
     * @param array $type
     * @param array $assetType
     * @param string $manufacturer
     * @param string $model
     * @return Commodity[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $code = '%', string $name = '%', $type = array(), $assetType = array(), string $manufacturer = '%', string $model = '%'): array
    {
        $query = "SELECT `id` FROM `ITSM_Commodity` WHERE `code` LIKE :code AND `name` LIKE :name AND `manufacturer` LIKE :manufacturer AND `model` LIKE :model";

        if(is_array($type) AND !empty($type))
        {
            $query .= " AND `commodityType` IN (SELECT `id` FROM `Attribute` WHERE `extension` = 'itsm' AND `type` = 'coty' AND `code` IN (" . self::getAttributeCodeString($type) . "))";
        }

        if(is_array($assetType) AND !empty($assetType))
        {
            $query .= " AND `assetType` IN (SELECT `id` FROM `Attribute` WHERE `extension` = 'itsm' AND `type` = 'asty' AND `code` IN (" . self::getAttributeCodeString($assetType) . "))";
        }

        $query .= " ORDER BY `code` ASC";

        $handler = new DatabaseConnection();

        $select = $handler->prepare($query);
        $select->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $select->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $select->bindParam('manufacturer', $manufacturer, DatabaseConnection::PARAM_STR);
        $select->bindParam('model', $model, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $commodities = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $commodities[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $commodities;
    }

    /**
     * @param string $code
     * @param string $name
     * @param int $commodityType
     * @param int $assetType
     * @param string $manufacturer
     * @param string $model
     * @param float $unitCost
     * @return Commodity
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $code, string $name, int $commodityType, int $assetType, string $manufacturer,
                                  string $model, float $unitCost): Commodity
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare("INSERT INTO `ITSM_Commodity` (`code`, `name`, `commodityType`, `assetType`, 
                            `manufacturer`, `model`, `unitCost`) 
                            VALUES (:code, :name, :commodityType, :assetType, :manufacturer, :model, :unitCost)");

        $insert->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $insert->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $insert->bindParam('commodityType', $commodityType, DatabaseConnection::PARAM_INT);
        $insert->bindParam('assetType', $assetType, DatabaseConnection::PARAM_INT);
        $insert->bindParam('manufacturer', $manufacturer, DatabaseConnection::PARAM_STR);
        $insert->bindParam('model', $model, DatabaseConnection::PARAM_STR);
        $insert->bindParam('unitCost', $unitCost, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $code
     * @param string $name
     * @param int $commodityType
     * @param int $assetType
     * @param string $manufacturer
     * @param string $model
     * @param float $unitCost
     * @return Commodity
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $code, string $name, int $commodityType, int $assetType,
                                  string $manufacturer, string $model, float $unitCost): Commodity
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare("UPDATE `ITSM_Commodity` SET `code` = :code, `name` = :name, 
                            `commodityType` = :commodityType, `assetType` = :assetType, `manufacturer` = :manufacturer, 
                            `model` = :model, `unitCost` = :unitCost WHERE `id` = :id");

        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $update->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $update->bindParam('commodityType', $commodityType, DatabaseConnection::PARAM_STR);
        $update->bindParam('assetType', $assetType, DatabaseConnection::PARAM_STR);
        $update->bindParam('manufacturer', $manufacturer, DatabaseConnection::PARAM_STR);
        $update->bindParam('model', $model, DatabaseConnection::PARAM_STR);
        $update->bindParam('unitCost', $unitCost, DatabaseConnection::PARAM_STR);
        $update->execute();

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

        $delete = $handler->prepare("DELETE FROM `ITSM_Commodity` WHERE `id` = ? ");
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

        $select = $handler->prepare("SELECT `id` FROM `ITSM_Commodity` WHERE `code` = ? LIMIT 1");
        $select->bindParam(1, $code, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectNameById(int $id): ?string
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `name` FROM `ITSM_Commodity` WHERE `id` = ?");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            return NULL;

        return $select->fetchColumn();
    }
}
