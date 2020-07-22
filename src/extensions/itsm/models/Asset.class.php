<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/10/2019
 * Time: 10:08 PM
 */


namespace extensions\itsm\models;


use extensions\itsm\database\AssetDatabaseHandler;
use exceptions\ValidationException;
use models\Model;

class Asset extends Model
{
    private const MESSAGES = array(
        'TAG_IN_USE' => 'Asset # is in use',
        'TAG_INVALID' => 'Asset # can only consist of digits',
        'SERIAL_NUMBER' => 'Serial number cannot exceed 64 characters'
    );

    private $id;
    private $commodity;
    private $warehouse;
    private $assetTag;
    private $parent;
    private $location;
    private $serialNumber;
    private $manufactureDate;
    private $purchaseOrder;
    private $notes;
    private $discardOrder;
    private $discarded;
    private $discardDate;
    private $verified;
    private $verifyDate;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getCommodity(): int
    {
        return $this->commodity;
    }

    /**
     * @return int|null
     */
    public function getWarehouse(): ?int
    {
        return $this->warehouse;
    }

    /**
     * @return int
     */
    public function getAssetTag(): int
    {
        return $this->assetTag;
    }

    /**
     * @return int|null
     */
    public function getParent(): ?int
    {
        return $this->parent;
    }

    /**
     * @return int|null
     */
    public function getLocation(): ?int
    {
        return $this->location;
    }

    /**
     * @return string
     */
    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    /**
     * @return string
     */
    public function getManufactureDate(): ?string
    {
        return $this->manufactureDate;
    }

    /**
     * @return int
     */
    public function getPurchaseOrder(): int
    {
        return $this->purchaseOrder;
    }

    /**
     * @return string
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @return int|null
     */
    public function getDiscardOrder(): ?int
    {
        return $this->discardOrder;
    }

    /**
     * @return int
     */
    public function getDiscarded(): int
    {
        return $this->discarded;
    }

    /**
     * @return string|null
     */
    public function getDiscardDate(): ?string
    {
        return $this->discardDate;
    }

    /**
     * @return int
     */
    public function getVerified(): int
    {
        return $this->verified;
    }

    /**
     * @return string|null
     */
    public function getVerifyDate(): ?string
    {
        return $this->verifyDate;
    }

    /**
     * @param string|null $assetTag
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateAssetTag(?string $assetTag): bool
    {
        // not null
        if($assetTag === NULL)
            throw new ValidationException(self::MESSAGES['TAG_INVALID'], ValidationException::VALUE_IS_NULL);

        // at least 1 character
        if(strlen($assetTag) < 1)
            throw new ValidationException(self::MESSAGES['TAG_INVALID'], ValidationException::VALUE_TOO_SHORT);

        // only numbers
        if(!ctype_digit($assetTag))
            throw new ValidationException(self::MESSAGES['TAG_INVALID'], ValidationException::VALUE_IS_NOT_VALID);

        // not in use
        if(AssetDatabaseHandler::selectIdByAssetTag($assetTag) !== NULL)
            throw new ValidationException(self::MESSAGES['TAG_IN_USE'], ValidationException::VALUE_ALREADY_TAKEN);

        return TRUE;
    }

    /**
     * @param string|null $serialNumber
     * @return bool
     * @throws ValidationException
     */
    public static function validateSerialNumber(?string $serialNumber): bool
    {
        // not null
        if($serialNumber === NULL)
            throw new ValidationException(self::MESSAGES['SERIAL_NUMBER'], ValidationException::VALUE_IS_NULL);

        // not greater than 64 chars
        if(strlen($serialNumber) > 64)
            throw new ValidationException(self::MESSAGES['SERIAL_NUMBER'], ValidationException::VALUE_TOO_LONG);

        return TRUE;
    }
}
