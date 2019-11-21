<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/21/2019
 * Time: 10:34 AM
 */


namespace business;


use database\BadLoginDatabaseHandler;
use utilities\Validator;

class BadLoginOperator extends Operator
{
    /**
     * @param string $username Username used in connection
     * @param string $ip IP address supplied by client
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function log(string $username, string $ip): bool
    {
        // Get the actual address of the request
        $sourceIP = $_SERVER['REMOTE_ADDR'];

        return BadLoginDatabaseHandler::insert($username, $ip, $sourceIP);
    }

    /**
     * @param string|null $usernameFilter
     * @param string|null $ipFilter
     * @param string|null $timeStart
     * @param string|null $timeEnd
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function search(?string $usernameFilter = '%', ?string $ipFilter = '%', ?string $timeStart = '1000-01-01', ?string $timeEnd = '9999-12-31'): array
    {
        if(!Validator::validDate($timeStart))
            $timeStart = '1000-01-01';
        if(!Validator::validDate($timeEnd))
            $timeEnd = '9999-12-31';

        $timeStart .= ' 00:00:00';
        $timeEnd .= ' 23:59:59';

        return BadLoginDatabaseHandler::select($timeStart, $timeEnd, $usernameFilter, $ipFilter);
    }
}