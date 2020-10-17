<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/05/2019
 * Time: 6:32 PM
 */


namespace extensions\itsm\utilities;


class Pinger
{
    /**
     * @param string $ipAddress
     * @return bool
     */
    public static function ping(string $ipAddress): bool
    {
        exec(sprintf('ping -c 1 -W 5 %s', escapeshellarg($ipAddress)), $res, $val);

        return $val === 0;
    }
}
