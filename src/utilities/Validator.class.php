<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/12/2019
 * Time: 10:32 PM
 */


namespace utilities;


use business\AttributeOperator;
use business\UserOperator;
use exceptions\ValidationError;
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

    public static function alnumColonDotOnly(string $value): bool
    {
        return preg_match("/^[A-Za-z0-9:\.]+$/", $value);
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function alnumDashSpaceOnly(string $value): bool
    {
        return preg_match("/^[A-Za-z0-9- .]+$/", $value);
    }

    public static function alnumDashSpaceSlashOnly(string $value): bool
    {
        return preg_match("/^[A-Za-z0-9- \/]+$/", $value);
    }

    public static function alnumDashSpacePeriodOnly(string $value): bool
    {
        return preg_match("/^[A-Za-z0-9- .]+$/", $value);
    }

    /**
     * @param string $mac
     * @return bool
     */
    public static function validMACAddress(string $mac): bool
    {
        return preg_match("/^[A-Za-z0-9:]+$/", $mac) == 1;
    }

    /**
     * @param string $domainName
     * @return bool
     */
    public static function validDomainName(string $domainName): bool
    {
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domainName)
            && preg_match("/^.{1,253}$/", $domainName)
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domainName));
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
     * @param string $path
     * @return bool
     */
    public static function validUnixFilePath(string $path): bool
    {
        return preg_match('/^\/[A-Za-z0-9-.\/]+$/', $path);
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
        else if(isset($rules['null']) AND $rules['null'] AND $value === NULL)
            return TRUE;

        // skip validation if empty string allowed, and string is empty
        if(isset($rules['empty']) AND $rules['empty'] AND strlen($value) === 0)
            return true;

        // Username
        if(isset($rules['username']))
        {
            if(UserOperator::idFromUsername($value) === NULL)
                throw new ValidationException("{$rules['name']} not found", ValidationException::VALUE_IS_NOT_VALID);
        }

        // Attribute
        if(isset($rules['attribute']) AND isset($rules['attrExtension']) AND isset($rules['attrType']))
        {
            if(!AttributeOperator::idFromCode($rules['attrExtension'], $rules['attrType'], (string)$value))
                throw new ValidationException("{$rules['name']} is not valid", ValidationException::VALUE_IS_NOT_VALID);
        }

        // Validate type
        if(isset($rules['type']) AND !(isset($rules['null']) AND $rules['null']))
        {
            // Valid date
            if($rules['type'] === 'date' AND !self::validDate($value))
                throw new ValidationException("{$rules['name']} must be a valid date", ValidationException::VALUE_IS_NOT_VALID);

            if($rules['type'] === 'email' AND !filter_var($value, FILTER_VALIDATE_EMAIL))
                throw new ValidationException("{$rules['name']} must be a valid email address", ValidationException::VALUE_IS_NOT_VALID);

            if($rules['type'] === 'int' AND !is_numeric($value))
                throw new ValidationException("{$rules['name']} must be a number", ValidationException::VALUE_IS_NOT_VALID);

            // Positive number
            if(isset($rules['positive']) AND $rules['positive'] AND in_array($rules['type'], array('int', 'float')) AND $value < 0)
                throw new ValidationException("{$rules['name']} must be positive",ValidationException::VALUE_IS_NOT_VALID);

        }

        // zero
        if(isset($rules['zero']) AND $rules['zero'] === FALSE AND $value == 0)
            throw new ValidationException("{$rules['name']} must not be zero",ValidationException::VALUE_IS_NOT_VALID);

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

        // alnum only
        if(isset($rules['alnum']) AND !ctype_alnum($value))
            throw new ValidationException("{$rules['name']} must consist of letters and numbers only", ValidationException::VALUE_IS_NOT_VALID);

        // alnumds
        if(isset($rules['alnumds']) AND !self::alnumDashSpaceOnly($value))
            throw new ValidationException("{$rules['name']} must consist of letters, numbers, '-', and spaces only", ValidationException::VALUE_IS_NOT_VALID);

        // alnumdss
        if(isset($rules['alnumdss']) AND !self::alnumDashSpaceSlashOnly($value))
            throw new ValidationException("{$rules['name']} must consist of letters, numbers, '-', '/', and spaces only", ValidationException::VALUE_IS_NOT_VALID);

        // alnumdsp
        if(isset($rules['alnumdss']) AND !self::alnumDashSpacePeriodOnly($value))
            throw new ValidationException("{$rules['name']} must consist of letters, numbers, '-', '.', and spaces only", ValidationException::VALUE_IS_NOT_VALID);

        // unixpath
        if(isset($rules['unixpath']) AND !self::validUnixFilePath($value))
            throw new ValidationException("{$rules['name']} must be a valid UNIX path", ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }

    /**
     * @param string $class
     * @param array $params The names of variables to validate
     * @param array $vals An associative array of params and values
     * @param bool $useUnderscored // Also uses validation functions starting with an underscore
     * @return bool
     * @throws ValidationError
     */
    public static function validateClass(string $class, array $params, array $vals, bool $useUnderscored = FALSE): bool
    {
        $errors = array();

        // Ensure that each param is represented in vals
        foreach($params as $param)
        {
            if(!isset($vals[$param]))
                $vals[$param] = NULL;
        }

        foreach(array_keys($vals) as $val)
        {
            $func = 'validate' . ucfirst($val);

            try{$class::$func($vals[$val]);}
            catch(ValidationException $e){$errors[] = $e->getMessage();}
            catch(\Error $e){} // Catch function not defined

            if($useUnderscored)
            {
                $func = '_validate' . ucfirst($val);
                try{$class::$func($vals[$val]);}
                catch(ValidationException $e){$errors[] = $e->getMessage();}
                catch(\Error $e){} // Catch function not defined
            }
        }

        if(!empty($errors))
            throw new ValidationError($errors);

        return TRUE;
    }
}
