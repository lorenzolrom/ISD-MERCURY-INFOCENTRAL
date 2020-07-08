<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/10/2019
 * Time: 3:09 PM
 */


namespace exceptions;


class ValidationException extends MercuryException
{
    const VALUE_IS_OK = 0;
    const VALUE_TOO_SHORT = 1;
    const VALUE_TOO_LONG = 2;
    const VALUE_ALREADY_TAKEN = 3;
    const VALUE_IS_NULL = 4;
    const VALUE_IS_NOT_VALID = 5;

    /**
     * @param string $date
     * @param string $format
     * @return bool
     */
    public static function validDate(string $date, string $format = "Y-m-d"): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}
