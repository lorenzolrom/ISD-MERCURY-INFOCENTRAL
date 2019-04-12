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

    public static function select(string $assetTag = '%', string $serialNumber = '%', array $inWarehouse = array(),
                                  array $isDiscarded = array(), string $buildingCode = '%', string $locationCode = '%',
                                  string $warehouseCode = '%', string $poNumber = '%', string $manufacturer = '%',
                                  string $model = '%', string $commodityCode = '%', string $commodityName = '%',
                                  array $commodityType = array(), array $assetType = array(),
                                  array $isVerified = array()): array
    {
        return array();
    }
}