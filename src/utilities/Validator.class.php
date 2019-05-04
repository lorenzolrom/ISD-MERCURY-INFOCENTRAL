<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/12/2019
 * Time: 10:32 PM
 */


namespace utilities;


use business\AttributeOperator;
use business\UserOperator;
use exceptions\ValidationException;

class Validator
{
    /**
     * @param string $value
     * @return bool
     */
    public static function alnumDashOnly(string $value): bool
    {
        return preg_match("/^[A-Za-z0-9-]+$/", $value);
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function alnumDashSpaceOnly(string $value): bool
    {
        return preg_match("/^[A-Za-z0-9- ]+$/", $value);
    }

    /**
     * @param string $mac
     * @return bool
     */
    public static function validMACAddress(string $mac): bool
    {
        return preg_match("/^[A-Za-z0-9-]+$/", $mac) == 1;
    }

    /**
     * @param string $domainName
     * @return bool
     */
    public static function validDomainName(string $domainName): bool
    {
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domainName)
            AND preg_match("/^.{1,253}$/", $domainName)
            AND preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domainName));
    }

    /**
     * @param $date
     * @param string $format
     * @return bool
     */
    public static function validDate($date, $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d AND $d->format($format) == $date;
    }

    /**
     * @param array $rules
     * @param $value
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validate(array $rules, $value): bool
    {
        /*
        $example = array(
            'number' => array(
                'type' => 'text', // or float, or int, or date
                'name' => 'Number', // For display
                'null' => false,
                'acceptable' => array(0, 1),
                'lower' => '1',
                'upper' => '2',
                'exact' => '5',
                'positive' => true
            )
        );
        */

        // null (default true)
        if((!isset($rules['null']) OR !$rules['null'])AND $value === NULL)
            throw new ValidationException($rules['name'] . ' is required', ValidationException::VALUE_IS_NULL);

        // Username
        if(isset($rules['username']))
        {
            if(UserOperator::idFromUsername($value) === NULL)
                throw new ValidationException("{$rules['name']} not found");
        }

        // Attribute
        if(isset($rules['attribute']) AND isset($rules['attrExtension']) AND isset($rules['attrType']))
        {
            if(!AttributeOperator::idFromCode($rules['attrExtension'], $rules['attrType'], (string)$value))
                throw new ValidationException("{$rules['name']} is not valid");
        }

        // Validate type
        if(isset($rules['type']))
        {
            // Valid date
            if($rules['type'] === 'date' AND !self::validDate($value))
                throw new ValidationException("{$rules['name']} must be a valid date");

            // Positive number
            if(isset($rules['positive']) AND $rules['positive'] AND in_array($rules['type'], array('int', 'float')) AND $value < 0)
                throw new ValidationException("{$rules['name']} must be positive");

        }

        // acceptable
        if(isset($rules['acceptable']) AND is_array($rules['acceptable']) AND !in_array($value, $rules['acceptable']))
            throw new ValidationException("{$rules['name']} is not valid", ValidationException::VALUE_IS_NOT_VALID);

        // exact
        if(isset($rules['exact']) AND strlen($value) !== $rules['exact'])
            throw new ValidationException("{$rules['name']} must be exactly {$rules['exact']} characters", ValidationException::VALUE_IS_NOT_VALID);

        // lower
        if(isset($rules['lower']) AND strlen($value) < $rules['lower'])
            throw new ValidationException("{$rules['name']} must be at least {$rules['lower']} characters", ValidationException::VALUE_TOO_SHORT);

        // upper
        if(isset($rules['upper']) AND strlen($value) > $rules['upper'])
            throw new ValidationException("{$rules['name']} must be no greater than {$rules['upper']} characters", ValidationException::VALUE_TOO_LONG);

        return TRUE;
    }
}