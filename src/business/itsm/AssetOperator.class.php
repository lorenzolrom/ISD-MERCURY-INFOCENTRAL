<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/11/2019
 * Time: 10:15 AM
 */


namespace business\itsm;


use business\Operator;
use database\itsm\AssetDatabaseHandler;
use models\itsm\Asset;

class AssetOperator extends Operator
{
    /**
     * @param int $assetTag
     * @return Asset
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getAsset(int $assetTag): Asset
    {
        return AssetDatabaseHandler::selectByAssetTag($assetTag);
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
    public static function search(string $assetTag = '%', string $serialNumber = '%', $inWarehouse = array(),
                                  $isDiscarded = array(), string $buildingCode = '%', string $locationCode = '%',
                                  string $warehouseCode = '%', string $poNumber = '%', string $manufacturer = '%',
                                  string $model = '%', string $commodityCode = '%', string $commodityName = '%',
                                  $commodityType = array(), $assetType = array(),
                                  $isVerified = array()): array
    {
        return AssetDatabaseHandler::select($assetTag, $serialNumber, $inWarehouse, $isDiscarded, $buildingCode, $locationCode, $warehouseCode, $poNumber, $manufacturer, $model, $commodityCode, $commodityName, $commodityType, $assetType, $isVerified);
    }
}