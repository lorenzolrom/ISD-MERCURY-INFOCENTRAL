<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 5/02/2019
 * Time: 8:49 PM
 */


namespace extensions\itsm\models;


use extensions\itsm\database\URLAliasDatabaseHandler;
use exceptions\ValidationException;
use models\Model;

class URLAlias extends Model
{
    private const MESSAGES = array(
        'ALIAS_LENGTH' => 'Alias must be between 1 and 64 characters',
        'ALIAS_INVALID' => 'Alias is not valid',
        'ALIAS_IN_USE' => 'Alias already in use',
        'DESTINATION' => 'Destination is required',
        'DISABLED' => 'Disabled value is not valid'
    );

    private $id;
    private $alias;
    private $destination;
    private $disabled;

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
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @return string
     */
    public function getDisabled(): string
    {
        return $this->disabled;
    }

    /**
     * @param string|null $alias
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateAlias(?string $alias): bool
    {
        // not null
        if($alias === NULL)
            throw new ValidationException(self::MESSAGES['ALIAS_LENGTH'], ValidationException::VALUE_IS_NULL);

        // not in use
        if(URLAliasDatabaseHandler::selectIdFromAlias($alias) !== NULL)
            throw new ValidationException(self::MESSAGES['ALIAS_IN_USE'], ValidationException::VALUE_ALREADY_TAKEN);

        // at least 1 character
        if(strlen($alias) < 1)
            throw new ValidationException(self::MESSAGES['ALIAS_LENGTH'], ValidationException::VALUE_TOO_SHORT);

        // no greater than 64 characters
        if(strlen($alias) > 64)
            throw new ValidationException(self::MESSAGES['ALIAS_LENGTH'], ValidationException::VALUE_TOO_LONG);

        return TRUE;
    }

    /**
     * @param string|null $destination
     * @return bool
     * @throws ValidationException
     */
    public static function validateDestination(?string $destination): bool
    {
        // not null
        if($destination === NULL)
            throw new ValidationException(self::MESSAGES['DESTINATION'], ValidationException::VALUE_IS_NULL);

        // greater than 1 character
        if(strlen($destination) < 1)
            throw new ValidationException(self::MESSAGES['DESTINATION'], ValidationException::VALUE_TOO_SHORT);

        return TRUE;
    }

    /**
     * @param string|null $disabled
     * @return bool
     * @throws ValidationException
     */
    public static function validateDisabled(?string $disabled): bool
    {
        // not null
        if($disabled === NULL)
            throw new ValidationException(self::MESSAGES['DISABLED'], ValidationException::VALUE_IS_NULL);

        // 0 or 1
        if(!in_array($disabled, array(0, 1)))
            throw new ValidationException(self::MESSAGES['DISABLED'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }
}
