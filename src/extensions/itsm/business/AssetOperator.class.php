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


namespace extensions\itsm\business;

use extensions\facilities\business\LocationOperator;
use business\Operator;
use extensions\itsm\database\AssetDatabaseHandler;
use extensions\itsm\database\AssetWorksheetDatabaseHandler;
use exceptions\EntryNotFoundException;
use exceptions\ValidationException;
use extensions\itsm\models\Asset;
use utilities\HistoryRecorder;

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

    /**
     * @param Asset $asset
     * @param string|null $assetTag
     * @param string|null $serialNumber
     * @param string|null $notes
     * @param string|null $manufactureDate
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function updateAsset(Asset $asset, ?string $assetTag, ?string $serialNumber, ?string $notes, ?string $manufactureDate): array
    {
        $errors = self::validateSubmission($assetTag, $serialNumber, $asset);

        if(!empty($errors))
            return array('errors' => $errors);

        HistoryRecorder::writeHistory('ITSM_Asset', HistoryRecorder::MODIFY, $asset->getId(), $asset, array('assetTag' => $assetTag, 'serialNumber' => $serialNumber, 'notes' => (string)$notes, 'manufactureDate' => (string)$manufactureDate));

        return array('id' => AssetDatabaseHandler::update($asset->getId(), $assetTag, $serialNumber, (string)$notes, (string)$manufactureDate));
    }

    /**
     * @param int|null $id
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function assetTagFromId(?int $id): ?string
    {
        return AssetDatabaseHandler::selectAssetTagById((int) $id);
    }

    /**
     * @param string|null $assetTag
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function idFromAssetTag(?string $assetTag): ?int
    {
        return AssetDatabaseHandler::selectIdByAssetTag((string)$assetTag);
    }

    /**
     * @param string $assetTag
     * @return Asset[]
     * @throws \exceptions\DatabaseException
     */
    public static function getChildren(string $assetTag): array
    {
        return AssetDatabaseHandler::selectAssetByParent($assetTag);
    }

    /**
     * @param string|null $assetTag
     * @param string|null $serialNumber
     * @param Asset|null $asset
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function validateSubmission(?string $assetTag, ?string $serialNumber, ?Asset $asset = NULL): array
    {
        $errors = array();

        if($asset === NULL OR $asset->getAssetTag() != $assetTag)
        {
            try{Asset::validateAssetTag($assetTag);}
            catch(ValidationException $e){$errors[] = $e->getMessage();}
        }

        try{Asset::validateSerialNumber($serialNumber);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        return $errors;
    }

    /**
     * @param Asset $asset
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function verifyAsset(Asset $asset): array
    {
        $errors = self::assetCanBeModified($asset);

        if(!empty($errors))
            return array('errors' => $errors);

        $verifyDate = date('Y-m-d');

        $vals = array(
            'verified' => 1,
            'verifyDate' => $verifyDate
        );

        HistoryRecorder::writeHistory('ITSM_Asset', HistoryRecorder::MODIFY, $asset->getId(), $asset, $vals);

        AssetDatabaseHandler::updateVerified($asset->getId(), 1, $verifyDate);

        return $errors;
    }

    /**
     * @param Asset $asset
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function unVerifyAsset(Asset $asset): array
    {
        $errors = self::assetCanBeModified($asset);

        if(!empty($errors))
            return array('errors' => $errors);

        $vals = array(
            'verified' => 0,
            'verifyDate' => NULL
        );

        HistoryRecorder::writeHistory('ITSM_Asset', HistoryRecorder::MODIFY, $asset->getId(), $asset, $vals, array('verifyDate'));

        AssetDatabaseHandler::updateVerified($asset->getId(), 0, NULL);

        return $errors;
    }

    /**
     * @param Asset $asset
     * @param int $parentAssetTag
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function linkToParent(Asset $asset, int $parentAssetTag): array
    {
        $errors = self::assetCanBeModified($asset);

        if(!empty($errors))
            return array('errors' => $errors);

        try
        {
            $parentAsset = AssetOperator::getAsset($parentAssetTag);

            if($asset->getId() === $parentAsset->getId())
                return array('errors' => array('Asset cannot be linked to itself'));

            if($parentAsset->getParent() === $asset->getId())
                return array('errors' => array('Parent Asset cannot be a child of this asset'));

            if($asset->getParent() !== NULL)
                return array('errors' => array('Asset already linked to parent'));

            HistoryRecorder::writeHistory('ITSM_Asset', HistoryRecorder::MODIFY, $asset->getId(), $asset, array('parent' => $parentAsset->getId()));

            AssetDatabaseHandler::updateParent($asset->getId(), $parentAsset->getId());
        }
        catch(EntryNotFoundException $e)
        {
            return array('errors' => array('Parent Asset not found'));
        }

        return array();
    }

    /**
     * @param Asset $asset
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function unlinkFromParent(Asset $asset): array
    {
        $errors = self::assetCanBeModified($asset);

        if(!empty($errors))
            return array('errors' => $errors);

        HistoryRecorder::writeHistory('ITSM_Asset', HistoryRecorder::MODIFY, $asset->getId(), $asset, array('parent' => NULL), array('parent'));

        AssetDatabaseHandler::updateParent($asset->getId(), NULL);

        return array();
    }

    /**
     * @param Asset $asset
     * @param string $buildingCode
     * @param string $locationCode
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function setLocation(Asset $asset, string $buildingCode, string $locationCode): array
    {
        $errors = self::assetCanBeModified($asset);
        $location = NULL;

        try
        {
            $location = LocationOperator::getLocationByCode($buildingCode, $locationCode);
        }
        catch(EntryNotFoundException $e)
        {
            $errors[] = 'Location not found';
        }

        if(!empty($errors))
            return array('errors' => $errors);

        $vals = array(
            'location' => $location->getId(),
            'warehouse' => NULL
        );

        HistoryRecorder::writeHistory('ITSM_Asset', HistoryRecorder::MODIFY, $asset->getId(), $asset, $vals, array('warehouse'));

        AssetDatabaseHandler::fullUpdate($asset->getId(), NULL, $asset->getAssetTag(), $asset->getParent(),
            $location->getId(), $asset->getSerialNumber(), $asset->getManufactureDate(), $asset->getNotes(),
            $asset->getDiscarded(), $asset->getDiscardDate(), $asset->getVerified(), $asset->getVerifyDate());

        // Also move children
        foreach(AssetOperator::getChildren($asset->getAssetTag()) as $child)
        {
            AssetOperator::setLocation($child, $buildingCode, $locationCode);
        }

        return array();
    }

    /**
     * @param Asset $asset
     * @param string $warehouseCode
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function setWarehouse(Asset $asset, string $warehouseCode): array
    {
        $errors = self::assetCanBeModified($asset);
        $warehouse = WarehouseOperator::idFromCode($warehouseCode);

        if($warehouse === NULL)
            $errors[] = 'Warehouse not found';

        if(!empty($errors))
            return array('errors' => $errors);

        $vals = array(
            'warehouse' => $warehouse,
            'location' => NULL
        );

        HistoryRecorder::writeHistory('ITSM_Asset', HistoryRecorder::MODIFY, $asset->getId(), $asset, $vals, array('location'));

        AssetDatabaseHandler::fullUpdate($asset->getId(), $warehouse, $asset->getAssetTag(), $asset->getParent(),
            NULL, $asset->getSerialNumber(), $asset->getManufactureDate(), $asset->getNotes(),
            $asset->getDiscarded(), $asset->getDiscardDate(), $asset->getVerified(), $asset->getVerifyDate());

        // Also move children
        foreach(AssetOperator::getChildren($asset->getAssetTag()) as $child)
        {
            AssetOperator::setWarehouse($child, $warehouseCode);
        }

        return array();
    }

    /**
     * @param Asset $asset
     * @return array
     */
    private static function assetCanBeModified(Asset $asset): array
    {
        $errors = array();

        if($asset->getDiscarded() == 1)
            $errors[] = 'Asset is discarded';

        return $errors;
    }

    /**
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function nextAssetTag(): int
    {
        return AssetDatabaseHandler::nextAssetTag();
    }

    /**
     * @param int $commodity
     * @param int $warehouse
     * @param int $assetTag
     * @param int $purchaseOrder
     * @return Asset
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function createAsset(int $commodity, int $warehouse, int $assetTag, int $purchaseOrder): Asset
    {
        $asset = AssetDatabaseHandler::insert($commodity, $warehouse, $assetTag, $purchaseOrder);

        HistoryRecorder::writeHistory('ITSM_Asset', HistoryRecorder::CREATE, $asset->getId(), $asset);

        return $asset;
    }

    /**
     * @param mixed $assets
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function addToWorksheet($assets): array
    {
        if(is_array($assets))
        {
            $assetIds = array();

            foreach($assets as $asset)
            {
                if(is_array($asset))
                    return array('errors' => array('One or more assets invalid'));

                $id = AssetOperator::idFromAssetTag($asset);

                if(AssetWorksheetDatabaseHandler::isAssetInWorksheet($id))
                    continue;

                if($id === NULL)
                    return array('errors' => array('One or more assets not found'));

                $assetIds[] = $id;
            }

            $count = AssetWorksheetDatabaseHandler::bulkAddToWorksheet($assetIds);
        }
        else
        {
            $id = AssetOperator::idFromAssetTag((int)$assets);
            if($id === NULL)
                return array('errors' => array('Asset not found'));
            else if(AssetWorksheetDatabaseHandler::isAssetInWorksheet((int)$id))
                return array('errors' => array('Asset already in worksheet'));

            $count = AssetWorksheetDatabaseHandler::addToWorksheet($assets);
        }

        return array('count' => $count);
    }

    /**
     * @param Asset $asset
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function removeFromWorksheet(Asset $asset): array
    {
        AssetWorksheetDatabaseHandler::removeFromWorksheet($asset->getId());

        return array();
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function clearWorksheet(): array
    {
        AssetWorksheetDatabaseHandler::clearWorksheet();

        return array();
    }

    /**
     * @return Asset[]
     * @throws \exceptions\DatabaseException
     */
    public static function getWorksheet(): array
    {
        return AssetWorksheetDatabaseHandler::getWorksheetAssets();
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function isAssetInWorksheet(int $id): bool
    {
        return AssetWorksheetDatabaseHandler::isAssetInWorksheet($id);
    }

    /**
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function getWorksheetCount(): int
    {
        return AssetWorksheetDatabaseHandler::getWorksheetCount();
    }

    /**
     * @param Asset $asset
     * @param int $discardOrder
     * @return bool
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function discard(Asset $asset, int $discardOrder): bool
    {
        $date = date('Y-m-d');
        HistoryRecorder::writeHistory('ITSM_Asset', HistoryRecorder::MODIFY, $asset->getId(), $asset, array(
            'discardOrder' => $discardOrder,
            'discarded' => 1,
            'discardDate' => $date,
            'location' => NULL,
            'warehouse' => NULL,
            'verified' => 0,
            'verifyDate' => NULL,
            'parent' => NULL,
        ), array(
            'location',
            'warehouse',
            'verifyDate',
            'parent'
        ));

        AssetDatabaseHandler::fullUpdate($asset->getId(), NULL, $asset->getAssetTag(), NULL,
            NULL, $asset->getSerialNumber(), $asset->getManufactureDate(), $asset->getNotes(), 1, $date,
            0, NULL);

        // Remove reference in child assets
        foreach(AssetOperator::getChildren($asset->getAssetTag()) as $child)
        {
            self::unlinkFromParent($child);
        }

        return TRUE;
    }
}