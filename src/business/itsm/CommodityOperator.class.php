<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/12/2019
 * Time: 3:19 PM
 */


namespace business\itsm;


use business\AttributeOperator;
use business\Operator;
use database\AttributeDatabaseHandler;
use database\itsm\AssetDatabaseHandler;
use database\itsm\CommodityDatabaseHandler;
use exceptions\EntryInUseException;
use exceptions\ValidationException;
use models\Attribute;
use models\itsm\Commodity;
use utilities\HistoryRecorder;

class CommodityOperator extends Operator
{
    /**
     * @param int $id
     * @return Commodity
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getCommodity(int $id): Commodity
    {
        return CommodityDatabaseHandler::selectById($id);
    }

    /**
     * @param string $code
     * @return Commodity
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getCommodityByCode(string $code): Commodity
    {
        return CommodityDatabaseHandler::selectByCode($code);
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
    public static function search(string $code = '%', string $name = '%', $type = array(), $assetType = array(), string $manufacturer = '%', string $model = '%'): array
    {
        return CommodityDatabaseHandler::select($code, $name, $type, $assetType, $manufacturer, $model);
    }

    /**
     * @param string|null $code
     * @param string|null $name
     * @param string|null $commodityTypeCode
     * @param string|null $assetTypeCode
     * @param string|null $manufacturer
     * @param string|null $model
     * @param float|null $unitCost
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function createCommodity(?string $code, ?string $name, ?string $commodityTypeCode,
                                           ?string $assetTypeCode, ?string $manufacturer, ?string $model,
                                           ?float $unitCost): array
    {
        $errors = self::validateSubmission($code, $name, $commodityTypeCode, $assetTypeCode, $manufacturer, $model, $unitCost);

        if(!empty($errors))
            return array('errors' => $errors);

        $commodityType = AttributeOperator::idFromCode('itsm', 'coty', $commodityTypeCode);
        $assetType = AttributeOperator::idFromCode('itsm', 'asty', $assetTypeCode);
        $unitCost = (float)$unitCost;

        $commodity = CommodityDatabaseHandler::insert($code, $name,
            $commodityType, $assetType, $manufacturer, $model, $unitCost);

        HistoryRecorder::writeHistory('ITSM_Commodity', HistoryRecorder::CREATE, $commodity->getId(),
            $commodity, array('code' => $code, 'name' => $name, 'commodityType' => $commodityType, 'assetType' => $assetType, 'manufacturer' => $manufacturer, 'model' => $model, 'unitCost' => $unitCost));

        return array('id' => $commodity->getId());
    }

    /**
     * @param Commodity $commodity
     * @param string|null $code
     * @param string|null $name
     * @param string|null $commodityTypeCode
     * @param string|null $assetTypeCode
     * @param string|null $manufacturer
     * @param string|null $model
     * @param float|null $unitCost
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function updateCommodity(Commodity $commodity, ?string $code, ?string $name,
                                           ?string $commodityTypeCode, ?string $assetTypeCode, ?string $manufacturer,
                                           ?string $model, ?float $unitCost): array
    {
        $errors = self::validateSubmission($code, $name, $commodityTypeCode, $assetTypeCode, $manufacturer, $model, $unitCost, $commodity);

        if(!empty($errors))
            return array('errors' => $errors);

        $commodityType = AttributeOperator::idFromCode('itsm', 'coty', $commodityTypeCode);
        $assetType = AttributeOperator::idFromCode('itsm', 'asty', $assetTypeCode);
        $unitCost = (float)$unitCost;

        HistoryRecorder::writeHistory('ITSM_Commodity', HistoryRecorder::MODIFY, $commodity->getId(),
            $commodity, array('code' => $code, 'name' => $name, 'commodityType' => $commodityType, 'assetType' => $assetType, 'manufacturer' => $manufacturer, 'model' => $model, 'unitCost' => $unitCost));

        $newCommodity = CommodityDatabaseHandler::update($commodity->getId(), $code, $name,
            $commodityType,$assetType, $manufacturer, $model, $unitCost);

        return array('id' => $newCommodity->getId());
    }

    /**
     * @param Commodity $commodity
     * @return bool
     * @throws EntryInUseException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function deleteCommodity(Commodity $commodity): bool
    {
        if(AssetDatabaseHandler::isCommodityTypeInUse($commodity->getId()))
            throw new EntryInUseException(EntryInUseException::MESSAGES[EntryInUseException::ENTRY_IN_USE], EntryInUseException::ENTRY_IN_USE);

        HistoryRecorder::writeHistory('ITSM_Commodity', HistoryRecorder::DELETE, $commodity->getId(), $commodity);

        return CommodityDatabaseHandler::delete($commodity->getId());
    }

    /**
     * @return Attribute[]
     * @throws \exceptions\DatabaseException
     */
    public static function getCommodityTypes(): array
    {
        return AttributeDatabaseHandler::select("itsm", "coty");
    }

    /**
     * @return Attribute[]
     * @throws \exceptions\DatabaseException
     */
    public static function getAssetTypes(): array
    {
        return AttributeDatabaseHandler::select("itsm", "asty");
    }

    /**
     * @param string $code
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function codeInUse(string $code): bool
    {
        return CommodityDatabaseHandler::codeInUse($code);
    }

    /**
     * @param int|null $id
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function assetTypeNameFromId(?int $id): ?string
    {
        return CommodityDatabaseHandler::selectNameById((int) $id);
    }

    /**
     * @param string|null $code
     * @param string|null $name
     * @param string|null $commodityTypeCode
     * @param string|null $assetTypeCode
     * @param string|null $manufacturer
     * @param string|null $model
     * @param float|null $unitCost
     * @param Commodity|null $commodity
     * @return array
     * @throws \exceptions\DatabaseException
     */
    private static function validateSubmission(?string $code, ?string $name, ?string $commodityTypeCode, ?string $assetTypeCode, ?string $manufacturer, ?string $model, ?float $unitCost, ?Commodity $commodity = NULL): array
    {
        $errors = array();

        // code
        if($commodity === NULL OR $commodity->getCode() != $code)
        {
            try{Commodity::validateCode($code);}
            catch(ValidationException $e){$errors[] = $e->getMessage();}
        }

        // name
        try{Commodity::validateName($name);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // commodity type code
        try{Commodity::validateCommodityType($commodityTypeCode);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // asset type code
        try{Commodity::validateAssetType($assetTypeCode);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // manufacturer
        try{Commodity::validateManufacturer($manufacturer);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // model
        try{Commodity::validateModel($model);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        try{Commodity::validateUnitCost((float)$unitCost);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        return $errors;
    }
}