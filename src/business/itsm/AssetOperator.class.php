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

        AssetDatabaseHandler::updateVerified($asset->getId(), 1, date('Y-m-d'), CurrentUserController::currentUser()->getId());

        return $errors;
    }

    /**
     * @param Asset $asset
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function unVerifyAsset(Asset $asset): array
    {
        $errors = self::assetCanBeModified($asset);

        if(!empty($errors))
            return array('errors' => $errors);

        AssetDatabaseHandler::updateVerified($asset->getId(), 0, NULL, NULL);

        return array('errors' => $errors);
    }

    public static function linkToParent(Asset $asset, ?string $parentAssetTag): array
    {
        return array();
    }

    public static function unlinkFromParent(Asset $asset): array
    {
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
}