<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/06/2019
 * Time: 11:41 AM
 */


namespace database;


abstract class DatabaseHandler
{
    /**
     * Converts an array of Attribute codes into a string that can be used in a WHERE IN clause.
     * Invalid values are omitted
     *
     * @param array $codes
     * @return string
     */
    protected static function getAttributeCodeString(array $codes): string
    {
        $array = array();

        foreach($codes as $code)
        {
            if(ctype_alnum($code) AND strlen($code) <= 4)
                $array[] = $code;
        }

        return "'" . implode("', '", $array) . "'";
    }

    /**
     * Converts an array that should only contain '0' and '1' into a string that can be used in a WHERE IN clause.
     * This is intended for checking fields that are TINYINT(1).
     * Invalid values are omitted.
     *
     * @param array $values
     * @return string
     */
    protected static function getBooleanString(array $values): string
    {
        $array = array();

        if(in_array(0, $values))
            $array[] = 0;

        if(in_array(1, $values))
            $array[] = 1;

        return "'" . implode("', '", $array) . "'";
    }

}