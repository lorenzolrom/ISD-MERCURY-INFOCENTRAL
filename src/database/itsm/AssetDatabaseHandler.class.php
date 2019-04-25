<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/11/2019
 * Time: 10:11 AM
 */


namespace database\itsm;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use models\itsm\Asset;

class AssetDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $assetTag
     * @return Asset
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectByAssetTag(int $assetTag): Asset
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id`, `commodity`, `warehouse`, `assetTag`, `parent`, `location`, `serialNumber`, 
                                            `manufactureDate`, `purchaseOrder`, `notes`, `createDate`, `discarded`, `discardDate`, 
                                            `lastModifyDate`, `lastModifyUser`, `verified`, `verifyDate`, `verifyUser` FROM 
                                            `ITSM_Asset` WHERE `assetTag` = ? LIMIT 1");

        $select->bindParam(1, $assetTag, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("models\itsm\Asset");
    }

    /**
     * @param string $assetTag
     * @param string $serialNumber
     * @param array $inWarehouse
     * @param array $isDiscarded
     * @param string $buildingCode
     * @param string $locationCode
     * @param string $warehouseCode
     * @param string $poNumber
     * @param string $manufacturer
     * @param string $model
     * @param string $commodityCode
     * @param string $commodityName
     * @param array $commodityType
     * @param array $assetType
     * @param array $isVerified
     * @return Asset[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $assetTag = '%', string $serialNumber = '%', $inWarehouse = array(),
                                  $isDiscarded = array(), string $buildingCode = '%', string $locationCode = '%',
                                  string $warehouseCode = '%', string $poNumber = '%', string $manufacturer = '%',
                                  string $model = '%', string $commodityCode = '%', string $commodityName = '%',
                                  $commodityType = array(), $assetType = array(),
                                  $isVerified = array()): array
    {
        $query = "SELECT `assetTag` FROM `ITSM_Asset` WHERE `assetTag` LIKE :assetTag AND IFNULL(`serialNumber`, '') LIKE :serialNumber
                                AND `commodity` IN 
                                (SELECT `id` FROM `ITSM_Commodity` WHERE `manufacturer` LIKE :manufacturer 
                                 AND `model` LIKE :model AND `code` LIKE :commodityCode AND `name` LIKE :commodityName)";

        // Warehouse code
        if(!in_array($warehouseCode, array('%', '%%')))
        {
            $checkWarehouse = TRUE;
            $query .= " AND IFNULL(`warehouse`, '') IN (SELECT `id` FROM `ITSM_Warehouse` WHERE `code` LIKE :warehouseCode)";
        }

        // Building
        if(!in_array($buildingCode, array('%', '%%')))
        {
            $checkBuilding = TRUE;
            $query .=  " AND IFNULL(`location`, '') IN (SELECT `id` FROM `FacilitiesCore_Location` WHERE `building` IN (SELECT `id` FROM `FacilitiesCore_Building` WHERE `code` LIKE :buildingCode))";
        }

        // Location
        if(!in_array($locationCode, array('%', '%%')))
        {
            $checkLocation = TRUE;
            $query .= " AND IFNULL(`location`, '') IN (SELECT `id` FROM `FacilitiesCore_Location` WHERE `code` LIKE :locationCode)";
        }

        // Purchase order
        if(!in_array($poNumber, array('%', '%%')))
        {
            $checkPoNumber = TRUE;
            $query .= " AND `purchaseOrder` IN (SELECT `id` FROM `ITSM_PurchaseOrder` WHERE `number` LIKE :poNumber)";
        }

        // In warehouse select
        if(is_array($inWarehouse) AND sizeof($inWarehouse) === 1)
        {
            if(in_array(1, $inWarehouse))
                $query .= " AND warehouse IS NOT NULL";
            if(in_array(0, $inWarehouse))
                $query .= " AND warehouse IS NULL";
        }

        // Is discarded select
        if(is_array($isDiscarded) AND !empty($isDiscarded))
        {
            $query .= " AND `discarded` IN (" . self::getBooleanString($isDiscarded) . ")";
        }

        // Is verified select
        if(is_array($isVerified) AND !empty($isVerified))
        {
            $query .= " AND `verified` IN (" . self::getBooleanString($isVerified) . ")";
        }

        // Commodity type
        if(is_array($commodityType) AND !empty($commodityType))
        {
            $query .= " AND `commodity` IN (SELECT `id` FROM `ITSM_Commodity` WHERE `commodityType` IN (SELECT `id` FROM `Attribute` WHERE `code` IN (" . self::getAttributeCodeString($commodityType) . ")))";
        }

        // Asset Type
        if(is_array($assetType) AND !empty($assetType))
        {
            $query .= " AND `commodity` IN (SELECT `id` FROM `ITSM_Commodity` WHERE `assetType` IN (SELECT `id` FROM `Attribute` WHERE `code` IN (" . self::getAttributeCodeString($assetType) . ")))";
        }

        $handler = new DatabaseConnection();

        $select = $handler->prepare($query);
        $select->bindParam('assetTag', $assetTag, DatabaseConnection::PARAM_STR);
        $select->bindParam('serialNumber', $serialNumber, DatabaseConnection::PARAM_STR);
        $select->bindParam('manufacturer', $manufacturer, DatabaseConnection::PARAM_STR);
        $select->bindParam('model', $model, DatabaseConnection::PARAM_STR);
        $select->bindParam('commodityCode', $commodityCode, DatabaseConnection::PARAM_STR);
        $select->bindParam('commodityName', $commodityName, DatabaseConnection::PARAM_STR);

        if(isset($checkWarehouse))
            $select->bindParam('warehouseCode', $warehouseCode, DatabaseConnection::PARAM_STR);

        if(isset($checkBuilding))
            $select->bindParam('buildingCode', $buildingCode, DatabaseConnection::PARAM_STR);

        if(isset($checkLocation))
            $select->bindParam('locationCode', $locationCode, DatabaseConnection::PARAM_STR);

        if(isset($checkPoNumber))
            $select->bindParam('poNumber', $poNumber, DatabaseConnection::PARAM_STR);

        $select->execute();

        $handler->close();

        $assets = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $assetTag)
        {
            try
            {
                $assets[] = self::selectByAssetTag($assetTag);
            }
            catch(EntryNotFoundException $e){}
        }

        return $assets;
    }

    /**
     * @param int $id
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectAssetTagById(int $id): ?string
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `assetTag` FROM `ITSM_Asset` WHERE `id` = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            return null;

        return $select->fetchColumn();
    }

    /**
     * @param int $commodityType
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function isCommodityTypeInUse(int $commodityType): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `ITSM_Asset` WHERE `commodity` = ? LIMIT 1");
        $select->bindParam(1, $commodityType, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $warehouseId
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function areAssetsInWarehouse(int $warehouseId): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `ITSM_Asset` WHERE `warehouse` = ? LIMIT 1");
        $select->bindParam(1, $warehouseId, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param string $parentAssetTag
     * @return Asset[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectAssetByParent(string $parentAssetTag): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `assetTag` FROM `ITSM_Asset` WHERE `parent` IN (SELECT `id` FROM `ITSM_Asset` WHERE `assetTag` = ?)");
        $select->bindParam(1, $parentAssetTag, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $children = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $assetTag)
        {
            try
            {
                $children[] = self::selectByAssetTag($assetTag);
            }
            catch(EntryNotFoundException $e){}
        }

        return $children;
    }
}