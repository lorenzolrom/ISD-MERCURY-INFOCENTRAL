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
}