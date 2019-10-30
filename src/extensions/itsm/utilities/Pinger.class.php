<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
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