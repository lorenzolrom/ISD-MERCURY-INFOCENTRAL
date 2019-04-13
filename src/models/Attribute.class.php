<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 5:50 PM
 */


namespace models;


use business\AttributeOperator;
use exceptions\ValidationException;
use utilities\Validator;

class Attribute extends Model
{
    private const MESSAGES = array(
        'EXTENSION' => 'Extension must be four letters',
        'TYPE' => 'Type must be four letters',
        'CODE' => 'Code must be four letters or numbers',
        'CODE_IN_USE' => 'Code is already in use',
        'NAME_LENGTH' => 'Name must be between 1 and 30 characters',
        'NAME_VALID' => 'Name must contain letters, numbers, spaces, and dashes only'
    );

    private $id;
    private $extension;
    private $type;
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
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
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
     * @param string|null $extension
     * @return bool
     * @throws ValidationException
     */
    public static function validateExtension(?string $extension): bool
    {
        if($extension === NULL)
            throw new ValidationException(self::MESSAGES['CODE'], ValidationException::VALUE_IS_NULL);

        if(strlen($extension) !== 4 OR !ctype_alpha($extension))
            throw new ValidationException(self::MESSAGES['CODE'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }

    /**
     * @param string|null $type
     * @return bool
     * @throws ValidationException
     */
    public static function validateType(?string $type): bool
    {
        if($type === NULL)
            throw new ValidationException(self::MESSAGES['TYPE'], ValidationException::VALUE_IS_NULL);

        if(strlen($type) !== 4 OR !ctype_alpha($type))
            throw new ValidationException(self::MESSAGES['TYPE'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }

    /**
     * @param string $extension
     * @param string $type
     * @param string|null $code
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateCode(?string $extension, ?string $type, ?string $code): bool
    {
        // not null
        if($code === NULL OR $extension === NULL OR $type === NULL)
            throw new ValidationException(self::MESSAGES['CODE'], ValidationException::VALUE_IS_NULL);

        // unique
        if(AttributeOperator::idFromCode($extension, $type, $code) !== NULL)
            throw new ValidationException(self::MESSAGES['CODE_IN_USE'], ValidationException::VALUE_ALREADY_TAKEN);

        // 4 characters
        // alnum only
        if(strlen($code) !== 4 OR !ctype_alnum($code))
            throw new ValidationException(self::MESSAGES['CODE'], ValidationException::VALUE_IS_NOT_VALID);

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

        // greater than 1 characters
        if(strlen($name) < 1)
            throw new ValidationException(self::MESSAGES['NAME_LENGTH'], ValidationException::VALUE_TOO_SHORT);

        // not more than 30 characters
        if(strlen($name) > 30)
            throw new ValidationException(self::MESSAGES['NAME_LENGTH'], ValidationException::VALUE_TOO_LONG);

        // only letters, numbers, dashes, spaces
        if(!Validator::alnumDashSpaceOnly($name))
            throw new ValidationException(self::MESSAGES['NAME_VALID'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }
}