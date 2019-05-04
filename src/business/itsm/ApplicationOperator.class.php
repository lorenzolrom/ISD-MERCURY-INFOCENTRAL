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
use database\itsm\ApplicationDatabaseHandler;
use database\itsm\ApplicationUpdateDatabaseHandler;
use models\Attribute;
use models\itsm\Application;
use models\itsm\ApplicationUpdate;

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

    /**
     * @param Application $application
     * @return ApplicationUpdate[]
     * @throws \exceptions\DatabaseException
     */
    public static function getApplicationUpdates(Application $application): array
    {
        return ApplicationUpdateDatabaseHandler::selectByApplication($application->getId());
    }

    /**
     * @param Application $application
     * @return ApplicationUpdate
     * @throws \exceptions\DatabaseException
     */
    public static function getLastUpdate(Application $application): ApplicationUpdate
    {
        return ApplicationUpdateDatabaseHandler::selectByApplication($application->getId(), 1)[0];
    }

    /**
     * @return Attribute[]
     * @throws \exceptions\DatabaseException
     */
    public static function getTypes(): array
    {
        return AttributeDatabaseHandler::select('itsm', 'aitt');
    }

    /**
     * @return Attribute[]
     * @throws \exceptions\DatabaseException
     */
    public static function getDataVolumes(): array
    {
        return AttributeDatabaseHandler::select('itsm', 'aitd');
    }

    /**
     * @return Attribute[]
     * @throws \exceptions\DatabaseException
     */
    public static function getLifeExpectancies(): array
    {
        return AttributeDatabaseHandler::select('itsm', 'aitl');
    }

    /**
     * @return Attribute[]
     * @throws \exceptions\DatabaseException
     */
    public static function getStatuses(): array
    {
        return AttributeDatabaseHandler::select('itsm', 'aits');
    }

    /**
     * @return Attribute[]
     * @throws \exceptions\DatabaseException
     */
    public static function getAuthTypes(): array
    {
        return AttributeDatabaseHandler::select('itsm', 'aita');
    }
}