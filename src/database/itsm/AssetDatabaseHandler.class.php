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
                                            `manufactureDate`, `purchaseOrder`, `notes`, `discardOrder`, `discarded`, `discardDate`, 
                                            `verified`, `verifyDate`, `verifyUser` FROM 
                                            `ITSM_Asset` WHERE `assetTag` = ? LIMIT 1");

        $select->bindParam(1, $assetTag, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

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
     * @param string $assetTag
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectIdByAssetTag(string $assetTag): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `ITSM_Asset` WHERE `assetTag` = ? LIMIT 1");
        $select->bindParam(1, $assetTag, DatabaseConnection::PARAM_STR);
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
     * @param int $assetTag
     * @return bool
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function isAssetDiscarded(int $assetTag): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `discarded` FROM `ITSM_Asset` WHERE `assetTag` = ? LIMIT 1');
        $select->bindParam(1, $assetTag, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        return $select->fetchColumn() == 1;
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

    /**
     * @param int $id
     * @param int $assetTag
     * @param string $serialNumber
     * @param string|null $notes
     * @param string|null $manufactureDate
     * @return Asset
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, int $assetTag, string $serialNumber, ?string $notes, ?string $manufactureDate): Asset
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare("UPDATE `ITSM_Asset` SET `assetTag` = :assetTag, `serialNumber` = :serialNumber, `notes` = :notes, `manufactureDate` = :manufactureDate WHERE `id` = :id");
        $update->bindParam('assetTag', $assetTag, DatabaseConnection::PARAM_INT);
        $update->bindParam('serialNumber', $serialNumber, DatabaseConnection::PARAM_STR);
        $update->bindParam('notes', $notes, DatabaseConnection::PARAM_STR);
        $update->bindParam('manufactureDate', $manufactureDate, DatabaseConnection::PARAM_STR);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->execute();

        $handler->close();

        return self::selectByAssetTag(self::selectAssetTagById($id));
    }

    /**
     * @param int $id
     * @param int|null $warehouse
     * @param string $assetTag
     * @param int|null $parent
     * @param int|null $location
     * @param string|null $serialNumber
     * @param string|null $manufactureDate
     * @param string|null $notes
     * @param int $discarded
     * @param string|null $discardDate
     * @param int $verified
     * @param string|null $verifyDate
     * @param int|null $verifyUser
     * @return Asset
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function fullUpdate(int $id, ?int $warehouse, string $assetTag, ?int $parent, ?int $location,
                                      ?string $serialNumber, ?string $manufactureDate, ?string $notes, int $discarded,
                                      ?string $discardDate, int $verified, ?string $verifyDate, ?int $verifyUser): Asset
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `ITSM_Asset` SET `warehouse` = :warehouse, `assetTag` = :assetTag, 
                        `parent` = :parent, `location` = :location, `serialNumber` = :serialNumber, 
                        `manufactureDate` = :manufactureDate, `notes` = :notes, `discarded` = :discarded, 
                        `discardDate` = :discardDate, `verified` = :verified, `verifyDate` = :verifyDate, 
                        `verifyUser` = :verifyUser WHERE `id` = :id');
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->bindParam('warehouse', $warehouse, DatabaseConnection::PARAM_INT);
        $update->bindParam('assetTag', $assetTag, DatabaseConnection::PARAM_STR);
        $update->bindParam('parent', $parent, DatabaseConnection::PARAM_INT);
        $update->bindParam('location', $location, DatabaseConnection::PARAM_INT);
        $update->bindParam('serialNumber', $serialNumber, DatabaseConnection::PARAM_STR);
        $update->bindParam('manufactureDate', $manufactureDate, DatabaseConnection::PARAM_STR);
        $update->bindParam('notes', $notes, DatabaseConnection::PARAM_STR);
        $update->bindParam('discarded', $discarded, DatabaseConnection::PARAM_INT);
        $update->bindParam('discardDate', $discardDate, DatabaseConnection::PARAM_STR);
        $update->bindParam('verified', $verified, DatabaseConnection::PARAM_INT);
        $update->bindParam('verifyDate', $verifyDate, DatabaseConnection::PARAM_STR);
        $update->bindParam('verifyUser', $verifyUser, DatabaseConnection::PARAM_INT);
        $update->execute();

        $handler->close();

        return self::selectByAssetTag(self::selectAssetTagById($id));
    }

    /**
     * @param int $id
     * @param int $verified
     * @param string|null $verifyDate
     * @param int|null $verifyUser
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function updateVerified(int $id, int $verified, ?string $verifyDate, ?int $verifyUser): bool
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `ITSM_Asset` SET `verified` = :verified, `verifyDate` = :verifyDate, `verifyUser` = :verifyUser WHERE `id` = :id');
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->bindParam('verified', $verified, DatabaseConnection::PARAM_INT);
        $update->bindParam('verifyDate', $verifyDate, DatabaseConnection::PARAM_STR);
        $update->bindParam('verifyUser', $verifyUser, DatabaseConnection::PARAM_INT);
        $update->execute();

        $handler->close();

        return $update->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param int|null $parent
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function updateParent(int $id, ?int $parent): bool
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `ITSM_Asset` SET `parent` = :parent WHERE `id` = :id');
        $update->bindParam('parent', $parent, DatabaseConnection::PARAM_INT);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->execute();

        $handler->close();

        return $update->getRowCount() === 1;
    }

    /**
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function nextAssetTag(): int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `assetTag` FROM `ITSM_Asset` ORDER BY `assetTag` DESC LIMIT 1');
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 0)
            return 1;
        else
            return $select->fetchColumn() + 1;
    }

    /**
     * @param int $commodity
     * @param int $warehouse
     * @param int $assetTag
     * @param int $purchaseOrder
     * @return Asset
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $commodity, int $warehouse, int $assetTag, int $purchaseOrder): Asset
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `ITSM_Asset` (`commodity`, `warehouse`, `assetTag`, `purchaseOrder`) VALUES (:commodity, :warehouse, :assetTag, :purchaseOrder)');
        $insert->bindParam('commodity', $commodity, DatabaseConnection::PARAM_INT);
        $insert->bindParam('warehouse', $warehouse, DatabaseConnection::PARAM_INT);
        $insert->bindParam('assetTag', $assetTag, DatabaseConnection::PARAM_INT);
        $insert->bindParam('purchaseOrder', $purchaseOrder, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $handler->close();

        return self::selectByAssetTag($assetTag);
    }
}