<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 9:29 AM
 */


namespace business\itsm;


use business\Operator;
use database\itsm\VHostDatabaseHandler;
use models\itsm\VHost;

class VHostOperator extends Operator
{
    /**
     * @param int $id
     * @return \models\itsm\VHost
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getVHost(int $id)
    {
        return VHostDatabaseHandler::selectById($id);
    }

    /**
     * @param string $domain
     * @param string $subdomain
     * @param string $name
     * @param string $assetTag
     * @param string $registrarCode
     * @param array $status
     * @return VHost[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(string $domain = "%", string $subdomain = "%", string $name = "%", string $assetTag = "%",
                                  string $registrarCode = "%", $status = array()): array
    {
        return VHostDatabaseHandler::select($domain, $subdomain, $name, $assetTag, $registrarCode, $status);
    }
}