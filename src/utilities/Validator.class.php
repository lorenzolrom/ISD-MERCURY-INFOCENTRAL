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
    public static function validMACAddress(string $mac)
    {
        return preg_match("/^[A-Za-z0-9-]+$/", $mac) == 1;
    }
}