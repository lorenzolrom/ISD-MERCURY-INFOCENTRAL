<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 9:08 AM
 */


namespace business;

use exceptions\ValidationException;

/**
 * Class BusinessHandler
 *
 * Parent class for handlers of business-logic and operations
 *
 * @package business
 */
abstract class Operator
{
    /**
     * @param string $class
     * @param array $vals
     * @return array
     */
    protected static function validate(string $class, array $vals): array
    {
        $errors = array();

        foreach(array_keys($vals) as $val)
        {
            $func = 'validate' . ucfirst($val);

            try{$class::$func($vals[$val]);}
            catch(ValidationException $e){$errors[] = $e->getMessage();}
            catch(\Error $e){} // Catch function not defined
        }

        return $errors;
    }
}