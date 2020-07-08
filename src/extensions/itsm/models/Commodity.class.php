<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/12/2019
 * Time: 2:53 PM
 */


namespace extensions\itsm\models;


use business\AttributeOperator;
use extensions\itsm\business\CommodityOperator;
use exceptions\ValidationException;
use models\Model;
use utilities\Validator;

class Commodity extends Model
{
    private const MESSAGES = array(
        'CODE_LENGTH' => 'Commodity code must be between 1 and 32 characters',
        'CODE_IN_USE' => 'Commodity code is already in use',
        'CODE_INVALID' => 'Commodity code must contain letters, numbers, and dashes only',
        'NAME_LENGTH' => 'Commodity name must be between 1 and 64 characters',
        'ASSET_TYPE' => 'Asset type is not valid',
        'COMMODITY_TYPE' => 'Commodity type is not valid',
        'MANUFACTURER' => 'Manufacturer is required',
        'MODEL' => 'Model is required',
        'UNIT_COST' => 'Unit cost must be a positive number'
    );

    private $id;
    private $code;
    private $name;
    private $commodityType;
    private $assetType;
    private $manufacturer;
    private $model;
    private $unitCost;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getCommodityType(): int
    {
        return $this->commodityType;
    }

    /**
     * @return int
     */
    public function getAssetType(): int
    {
        return $this->assetType;
    }

    /**
     * @return string
     */
    public function getManufacturer(): string
    {
        return $this->manufacturer;
    }

    /**
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @return float
     */
    public function getUnitCost(): float
    {
        return $this->unitCost;
    }

    /**
     * @param string|null $code
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateCode(?string $code): bool
    {
        // Not null
        if($code === NULL)
            throw new ValidationException(self::MESSAGES['CODE_LENGTH'], ValidationException::VALUE_IS_NULL);

        // Not in use
        if(CommodityOperator::codeInUse($code))
            throw new ValidationException(self::MESSAGES['CODE_IN_USE'], ValidationException::VALUE_ALREADY_TAKEN);

        // Greater than 1 character
        if(strlen($code) < 1)
            throw new ValidationException(self::MESSAGES['CODE_LENGTH'], ValidationException::VALUE_TOO_SHORT);

        // Less than 32 characters
        if(strlen($code) > 32)
            throw new ValidationException(self::MESSAGES['CODE_LENGTH'], ValidationException::VALUE_TOO_LONG);

        // Contains valid characters
        if(!Validator::alnumDashOnly($code))
            throw new ValidationException(self::MESSAGES['CODE_INVALID'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }

    /**
     * @param string|null $name
     * @return bool
     * @throws ValidationException
     */
    public static function validateName(?string $name): bool
    {
        // Not null
        if($name === NULL)
            throw new ValidationException(self::MESSAGES['NAME_LENGTH'], ValidationException::VALUE_IS_NULL);

        // Greater than 1 character
        if(strlen($name) < 1)
            throw new ValidationException(self::MESSAGES['NAME_LENGTH'], ValidationException::VALUE_TOO_SHORT);

        // Less than 64 characters
        if(strlen($name) > 64)
            throw new ValidationException(self::MESSAGES['NAME_LENGTH'], ValidationException::VALUE_TOO_LONG);

        return TRUE;
    }

    /**
     * @param string|null $commodityTypeCode
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateCommodityType(?string $commodityTypeCode): bool
    {
        if($commodityTypeCode === NULL)
            throw new ValidationException(self::MESSAGES['COMMODITY_TYPE'], ValidationException::VALUE_IS_NULL);

        if(AttributeOperator::idFromCode('itsm', 'coty', $commodityTypeCode) === NULL)
            throw new ValidationException(self::MESSAGES['COMMODITY_TYPE'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }

    /**
     * @param string|null $assetTypeCode
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateAssetType(?string $assetTypeCode): bool
    {
        if($assetTypeCode === NULL)
            throw new ValidationException(self::MESSAGES['ASSET_TYPE'], ValidationException::VALUE_IS_NULL);

        if(AttributeOperator::idFromCode('itsm', 'asty', $assetTypeCode) === NULL)
            throw new ValidationException(self::MESSAGES['ASSET_TYPE'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }

    /**
     * @param string|null $manufacturer
     * @return bool
     * @throws ValidationException
     */
    public static function validateManufacturer(?string $manufacturer): bool
    {
        // Not null
        if($manufacturer === NULL)
            throw new ValidationException(self::MESSAGES['MANUFACTURER'], ValidationException::VALUE_IS_NULL);

        // Greater than 1 character
        if(strlen($manufacturer) < 1)
            throw new ValidationException(self::MESSAGES['MANUFACTURER'], ValidationException::VALUE_TOO_SHORT);

        return TRUE;
    }

    /**
     * @param string|null $model
     * @return bool
     * @throws ValidationException
     */
    public static function validateModel(?string $model): bool
    {
        // Not null
        if($model === NULL)
            throw new ValidationException(self::MESSAGES['MODEL'], ValidationException::VALUE_IS_NULL);

        // Greater than 1 character
        if(strlen($model) < 1)
            throw new ValidationException(self::MESSAGES['MODEL'], ValidationException::VALUE_TOO_SHORT);

        return TRUE;
    }

    /**
     * @param float|null $unitCost
     * @return bool
     * @throws ValidationException
     */
    public static function validateUnitCost(?float $unitCost): bool
    {
        // Not null
        if($unitCost === NULL)
            throw new ValidationException(self::MESSAGES['UNIT_COST'], ValidationException::VALUE_IS_NULL);

        if($unitCost < 0)
            throw new ValidationException(self::MESSAGES['UNIT_COST'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }
}
