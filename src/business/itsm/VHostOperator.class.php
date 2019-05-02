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
use database\AttributeDatabaseHandler;
use database\itsm\VHostDatabaseHandler;
use models\Attribute;
use models\itsm\VHost;

class VHostOperator extends Operator
{
    /**
     * @param int $id
     * @return \models\itsm\VHost
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getVHost(int $id):VHost
    {
        return VHostDatabaseHandler::selectById($id);
    }

    /**
     * @param string $domain
     * @param string $subdomain
     * @param string $name
     * @param string $host
     * @param string $registrarCode
     * @param array $status
     * @return VHost[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(string $domain = "%", string $subdomain = "%", string $name = "%", string $host = "%",
                                  string $registrarCode = "%", $status = array()): array
    {
        return VHostDatabaseHandler::select($domain, $subdomain, $name, $host, $registrarCode, $status);
    }

    /**
     * @return Attribute[]
     * @throws \exceptions\DatabaseException
     */
    public static function getStatuses(): array
    {
        return AttributeDatabaseHandler::select('itsm', VHost::STATUS_ATTRIBUTE_TYPE);
    }
}