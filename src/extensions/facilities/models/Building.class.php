<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/09/2019
 * Time: 8:53 PM
 */


namespace extensions\facilities\models;


use extensions\facilities\business\BuildingOperator;
use extensions\facilities\business\LocationOperator;
use exceptions\ValidationException;
use models\Model;
use utilities\Validator;

class Building extends Model
{
    private const MESSAGES = array(
        'CODE_LENGTH' => 'Building code must be between 1 and 32 characters',
        'CODE_UNIQUE' => 'Building code already in use',
        'CODE_INVALID' => 'Building code must contain letters, numbers, and dashes only',
        'NAME_LENGTH' => 'Building name must be between 1 and 64 characters',
        'ADDRESS' => 'Address required',
        'CITY' => 'City required',
        'STATE' => 'State must be 2 characters',
        'ZIP_CODE' => 'Zip code must be 5 digits'
    );

    private $id;
    private $code;
    private $name;
    private $streetAddress;
    private $city;
    private $state;
    private $zipCode;

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
     * @return Location[]
     * @throws \exceptions\DatabaseException
     */
    public function getLocations(): array
    {
        return LocationOperator::getLocationsByBuilding($this);
    }

    /**
     * @param string|null $code
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateCode(?string $code): bool
    {
        // not null
        if($code === NULL)
            throw new ValidationException(self::MESSAGES['CODE_LENGTH'], ValidationException::VALUE_IS_NULL);

        // not already taken
        if(!BuildingOperator::codeIsUnique($code))
            throw new ValidationException(self::MESSAGES['CODE_UNIQUE'], ValidationException::VALUE_ALREADY_TAKEN);

        // at least 1 character
        if(strlen($code) < 1)
            throw new ValidationException(self::MESSAGES['CODE_LENGTH'], ValidationException::VALUE_TOO_SHORT);

        // not greater than 32 characters
        if(strlen($code) > 32)
            throw new ValidationException(self::MESSAGES['CODE_LENGTH'], ValidationException::VALUE_TOO_LONG);

        // valid characters
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
        // not null
        if($name === NULL)
            throw new ValidationException(self::MESSAGES['NAME_LENGTH'], ValidationException::VALUE_IS_NULL);

        // at least 1 character
        if(strlen($name) < 1)
            throw new ValidationException(self::MESSAGES['NAME_LENGTH'], ValidationException::VALUE_TOO_SHORT);

        return TRUE;
    }

    /**
     * @param string|null $streetAddress
     * @return bool
     * @throws ValidationException
     */
    public static function validateStreetAddress(?string $streetAddress): bool
    {
        // not null
        if($streetAddress === NULL)
            throw new ValidationException(self::MESSAGES['ADDRESS'], ValidationException::VALUE_IS_NULL);

        // at least 1 character
        if(strlen($streetAddress) < 1)
            throw new ValidationException(self::MESSAGES['ADDRESS'], ValidationException::VALUE_TOO_SHORT);

        return TRUE;
    }

    /**
     * @param string|null $city
     * @return bool
     * @throws ValidationException
     */
    public static function validateCity(?string $city): bool
    {
        // not null
        if($city === NULL)
            throw new ValidationException(self::MESSAGES['CITY'], ValidationException::VALUE_IS_NULL);

        // at least 1 character
        if(strlen($city) < 1)
            throw new ValidationException(self::MESSAGES['CITY'], ValidationException::VALUE_TOO_SHORT);

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

        // at least 1 character
        if(strlen($state) != 2)
            throw new ValidationException(self::MESSAGES['STATE'], ValidationException::VALUE_IS_NOT_VALID);

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

        // at least 1 character
        if(strlen($zipCode) != 5)
            throw new ValidationException(self::MESSAGES['ZIP_CODE'], ValidationException::VALUE_IS_NOT_VALID);

        // only contains digits
        if(!ctype_digit($zipCode))
            throw new ValidationException(self::MESSAGES['ZIP_CODE'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }
}
