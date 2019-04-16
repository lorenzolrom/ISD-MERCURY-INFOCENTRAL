<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/10/2019
 * Time: 10:03 PM
 */


namespace models\itsm;

use business\itsm\WarehouseOperator;
use exceptions\ValidationException;
use utilities\Validator;

class Warehouse extends ITSMModel
{
    private const MESSAGES = array(
        'CODE_LENGTH' => 'Warehouse code must be between 1 and 32 characters',
        'CODE_IN_USE' => 'Warehouse code is already in use',
        'CODE_INVALID' => 'Warehouse code must contain letters, numbers, and dashes only',
        'NAME_LENGTH' => 'Warehouse name must be between 1 and 64 characters',
    );

    private $id;
    private $code;
    private $name;
    private $closed;

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
    public function getClosed(): int
    {
        return $this->closed;
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
        if(WarehouseOperator::codeInUse($code))
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
}