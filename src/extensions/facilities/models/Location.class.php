<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/09/2019
 * Time: 8:55 PM
 */


namespace extensions\facilities\models;


use extensions\facilities\business\LocationOperator;
use exceptions\ValidationException;
use models\Model;
use utilities\Validator;

class Location extends Model
{
    private const MESSAGES = array(
        'CODE_LENGTH' => 'Location code must be between 1 and 32 characters',
        'CODE_UNIQUE' => 'Location code already in use',
        'CODE_INVALID' => 'Location code must contain letters, numbers, and dashes only',
        'NAME_LENGTH' => 'Location name must be between 1 and 64 characters',
        'BUILDING' => 'Building is not valid'
    );

    private $id;
    private $building;
    private $code;
    private $name;

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
    public function getBuilding(): int
    {
        return $this->building;
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
     * @param Building $building
     * @param string|null $code
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateCode(Building $building, ?string $code): bool
    {
        // not null
        if($code === NULL)
            throw new ValidationException(self::MESSAGES['CODE_LENGTH'], ValidationException::VALUE_IS_NULL);

        // at least 1 character
        if(strlen($code) < 1)
            throw new ValidationException(self::MESSAGES['CODE_LENGTH'], ValidationException::VALUE_TOO_SHORT);

        // not greater than 32 characters
        if(strlen($code) > 32)
            throw new ValidationException(self::MESSAGES['CODE_LENGTH'], ValidationException::VALUE_TOO_LONG);

        // not already taken
        if(!LocationOperator::codeIsUnique($building, $code))
            throw new ValidationException(self::MESSAGES['CODE_UNIQUE'], ValidationException::VALUE_ALREADY_TAKEN);

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
}
