<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/06/2019
 * Time: 10:33 AM
 */


namespace extensions\itsm\models;


use extensions\itsm\database\RegistrarDatabaseHandler;
use exceptions\ValidationException;
use models\Model;
use utilities\Validator;

class Registrar extends Model
{
    private const MESSAGES = array(
        'CODE_LENGTH' => 'Code must be between 1 and 32 characters',
        'CODE_INVALID' => 'Code must consist of letters, numbers, and - only',
        'CODE_TAKEN' => 'Code is already taken',
        'NAME_REQUIRED' => 'Name is required',
        'PHONE_LENGTH' => 'Phone must be no greater than 20 characters'
    );

    private $id;
    private $code;
    private $name;
    private $url;
    private $phone;

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
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
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

        // not taken
        if(RegistrarDatabaseHandler::codeInUse($code))
            throw new ValidationException(self::MESSAGES['CODE_TAKEN'], ValidationException::VALUE_ALREADY_TAKEN);

        // greater than 1 character
        if(strlen($code) < 1)
            throw new ValidationException(self::MESSAGES['CODE_LENGTH'], ValidationException::VALUE_TOO_SHORT);

        // no greater than 32
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
        if($name === NULL)
            throw new ValidationException(self::MESSAGES['NAME_REQUIRED'], ValidationException::VALUE_IS_NULL);

        // at least 1 character
        if(strlen($name) < 1)
            throw new ValidationException(self::MESSAGES['NAME_REQUIRED'], ValidationException::VALUE_TOO_SHORT);

        return TRUE;
    }

    /**
     * @param string|null $phone
     * @return bool
     * @throws ValidationException
     */
    public static function validatePhone(?string $phone): bool
    {
        // If set, no greater than 20 characters
        if($phone !== NULL AND strlen($phone) > 20)
            throw new ValidationException(self::MESSAGES['PHONE_LENGTH'], ValidationException::VALUE_TOO_LONG);

        return TRUE;
    }
}
