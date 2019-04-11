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
use database\itsm\ApplicationDatabaseHandler;
use models\itsm\Application;

class ApplicationOperator extends Operator
{
    /**
     * @param int $id
     * @param bool $useNumber
     * @return Application
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getApplication(int $id, bool $useNumber = FALSE): Application
    {
        if($useNumber)
            return ApplicationDatabaseHandler::selectByNumber($id);

        return ApplicationDatabaseHandler::selectById($id);
    }

    /**
     * @param string $number
     * @param string $name
     * @param string $description
     * @param string $ownerUsername
     * @param array $type
     * @param array $publicFacing
     * @param array $lifeExpectancy
     * @param array $dataVolume
     * @param array $authType
     * @param string $port
     * @param string $host
     * @param string $vhost
     * @param array $status
     * @return Application[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(string $number = "%", string $name = "%", string $description = "%", string $ownerUsername = "%", $type = array(),
                                  $publicFacing = array(), $lifeExpectancy = array(), $dataVolume = array(), $authType = array(), string $port = "%",
                                  string $host = "%", string $vhost = "%", $status = array()): array
    {
        return ApplicationDatabaseHandler::select($number, $name, $description, $ownerUsername, $type, $publicFacing, $lifeExpectancy,
            $dataVolume, $authType, $port, $host, $vhost, $status);
    }
}