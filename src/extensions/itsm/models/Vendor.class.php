<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 4/10/2019
 * Time: 10:04 PM
 */


namespace extensions\itsm\models;


use extensions\itsm\database\VendorDatabaseHandler;
use exceptions\ValidationException;
use models\Model;
use utilities\Validator;

class Vendor extends Model
{
    private const MESSAGES = array(
        'CODE_LENGTH' => 'Vendor code must be between 1 and 32 characters',
        'CODE_VALID' => 'Vendor code must consist of letter, numbers, and dashes only',
        'CODE_IN_USE' => 'Vendor code already in use',
        'NAME' => 'Vendor name is required',
        'STREET_ADDRESS' => 'Street address is required',
        'CITY' => 'City is required',
        'STATE' => 'State must be no greater than two characters',
        'ZIP_CODE' => 'Zip code must be no greater than five characters',
        'PHONE' => 'Phone must be no greater than 20 characters',
        'FAX' => 'Fax number must be no greater than 20 characters'
    );

    private $id;
    private $code;
    private $name;
    private $streetAddress;
    private $city;
    private $state;
    private $zipCode;
    private $phone;
    private $fax;

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
     * @return string
     */
    public function getStreetAddress(): string
    {
        return $this->streetAddress;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @return string|null
     */
    public function getFax(): ?string
    {
        return $this->fax;
    }

    /**
     * @param string|null $code
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateCode(?string $code): bool
    {
        // is not null
        if($code === NULL)
            throw new ValidationException(self::MESSAGES['CODE_LENGTH'], ValidationException::VALUE_IS_NULL);

        // is unique
        if(VendorDatabaseHandler::selectIdFromCode($code) !== NULL)
            throw new ValidationException(self::MESSAGES['CODE_IN_USE'], ValidationException::VALUE_ALREADY_TAKEN);

        // is greater than 1 character
        if(strlen($code) < 1)
            throw new ValidationException(self::MESSAGES['CODE_LENGTH'], ValidationException::VALUE_TOO_SHORT);

        // is no greater than 32 characters
        if(strlen($code) > 32)
            throw new ValidationException(self::MESSAGES['CODE_LENGTH'], ValidationException::VALUE_TOO_LONG);

        // is valid
        if(!Validator::alnumDashOnly($code))
            throw new ValidationException(self::MESSAGES['CODE_VALID'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }

    /**
     * @param string|null $name
     * @return bool
     * @throws ValidationException
     */
    public static function validateName(?string $name): bool
    {
        if($name === NULL)
            throw new ValidationException(self::MESSAGES['NAME'], ValidationException::VALUE_IS_NULL);

        if(strlen($name) < 1)
            throw new ValidationException(self::MESSAGES['NAME'], ValidationException::VALUE_TOO_SHORT);

        return TRUE;
    }

    /**
     * @param string|null $streetAddress
     * @return bool
     * @throws ValidationException
     */
    public static function validateStreetAddress(?string $streetAddress): bool
    {
        if($streetAddress === NULL)
            throw new ValidationException(self::MESSAGES['STREET_ADDRESS'], ValidationException::VALUE_IS_NULL);

        return TRUE;
    }

    /**
     * @param string|null $city
     * @return bool
     * @throws ValidationException
     */
    public static function validateCity(?string $city): bool
    {
        if($city === NULL)
            throw new ValidationException(self::MESSAGES['CITY'], ValidationException::VALUE_IS_NULL);

        return TRUE;
    }

    /**
     * @param string|null $state
     * @return bool
     * @throws ValidationException
     */
    public static function validateState(?string $state): bool
    {
        // not null
        if($state === NULL)
            throw new ValidationException(self::MESSAGES['STATE'], ValidationException::VALUE_IS_NULL);

        // not greater than 2 characters
        if(strlen($state) > 2)
            throw new ValidationException(self::MESSAGES['STATE'], ValidationException::VALUE_TOO_LONG);

        return TRUE;
    }

    /**
     * @param string|null $zipCode
     * @return bool
     * @throws ValidationException
     */
    public static function validateZipCode(?string $zipCode): bool
    {
        // not null
        if($zipCode === NULL)
            throw new ValidationException(self::MESSAGES['ZIP_CODE'], ValidationException::VALUE_IS_NULL);

        // not greater than 5 characters
        if(strlen($zipCode) > 5)
            throw new ValidationException(self::MESSAGES['ZIP_CODE'], ValidationException::VALUE_TOO_LONG);

        return TRUE;
    }

    /**
     * @param string|null $phone
     * @return bool
     * @throws ValidationException
     */
    public static function validatePhone(?string $phone): bool
    {
        if($phone === NULL)
            throw new ValidationException(self::MESSAGES['PHONE'], ValidationException::VALUE_IS_NULL);

        // not greater than 20 characters
        if(strlen($phone) > 20)
            throw new ValidationException(self::MESSAGES['PHONE'], ValidationException::VALUE_IS_NULL);

        return TRUE;
    }

    /**
     * @param string|null $fax
     * @return bool
     * @throws ValidationException
     */
    public static function validateFax(?string $fax): bool
    {
        // not greater than 20 characters
        if($fax !== NULL AND strlen($fax) > 20)
            throw new ValidationException(self::MESSAGES['FAX'], ValidationException::VALUE_IS_NULL);

        return TRUE;
    }
}
