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
use controllers\CurrentUserController;
use database\itsm\AssetDatabaseHandler;
use exceptions\EntryNotFoundException;
use exceptions\ValidationException;
use models\itsm\Asset;
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
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function updateAsset(Asset $asset, ?string $assetTag, ?string $serialNumber, ?string $notes): array
    {
        $errors = self::validateSubmission($assetTag, $serialNumber, $asset);

        if(!empty($errors))
            return array('errors' => $errors);

        HistoryRecorder::writeHistory('ITSM_Asset', HistoryRecorder::MODIFY, $asset->getId(), $asset, array('assetTag' => $assetTag, 'serialNumber' => $serialNumber, 'notes' => $notes));

        return array('id' => AssetDatabaseHandler::update($asset->getId(), $assetTag, $serialNumber, $notes));
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
            'verifyUser' => CurrentUserController::currentUser()->getId(),
            'verifyDate' => $verifyDate
        );

        HistoryRecorder::writeHistory('ITSM_Asset', HistoryRecorder::MODIFY, $asset->getId(), $asset, $vals);

        AssetDatabaseHandler::updateVerified($asset->getId(), 1, $verifyDate, CurrentUserController::currentUser()->getId());

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
            'verifyUser' => NULL,
            'verifyDate' => NULL
        );

        HistoryRecorder::writeHistory('ITSM_Asset', HistoryRecorder::MODIFY, $asset->getId(), $asset, $vals);

        AssetDatabaseHandler::updateVerified($asset->getId(), 0, NULL, NULL);

        return $errors;
    }

    /**
     * @param Asset $asset
     * @param int $parentAssetTag
     * @return array
     * @throws EntryNotFoundException
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
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private static function assetCanBeModified(Asset $asset): array
    {
        $errors = array();

        if(AssetDatabaseHandler::isAssetDiscarded($asset->getAssetTag()))
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
}