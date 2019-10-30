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


namespace extensions\itsm\business;


use business\AttributeOperator;
use business\Operator;
use business\UserOperator;
use database\AttributeDatabaseHandler;
use extensions\itsm\database\ApplicationDatabaseHandler;
use models\Attribute;
use extensions\itsm\models\Application;
use extensions\itsm\models\Host;
use extensions\itsm\models\VHost;
use utilities\HistoryRecorder;

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

    /**
     * @param Application $application
     * @return VHost[]
     * @throws \exceptions\DatabaseException
     */
    public static function getVHosts(Application $application): array
    {
        return ApplicationDatabaseHandler::getVHosts($application->getId());
    }

    /**
     * @param Application $application
     * @return Host[]
     * @throws \exceptions\DatabaseException
     */
    public static function getWebHosts(Application $application): array
    {
        return ApplicationDatabaseHandler::getHosts($application->getId(), 'webh');
    }

    /**
     * @param Application $application
     * @return Host[]
     * @throws \exceptions\DatabaseException
     */
    public static function getDataHosts(Application $application): array
    {
        return ApplicationDatabaseHandler::getHosts($application->getId(), 'data');
    }

    /**
     * @param Application $application
     * @return Host[]
     * @throws \exceptions\DatabaseException
     */
    public static function getAppHosts(Application $application): array
    {
        return ApplicationDatabaseHandler::getHosts($application->getId(), 'apph');
    }

    /**
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function createApplication(array $vals): array
    {
        self::validate('extensions\itsm\models\Application', $vals);

        $vals['owner'] = UserOperator::idFromUsername($vals['owner']);
        $vals['type'] = AttributeOperator::idFromCode('itsm', 'aitt', $vals['type']);
        $vals['lifeExpectancy'] = AttributeOperator::idFromCode('itsm', 'aitl', $vals['lifeExpectancy']);
        $vals['dataVolume'] = AttributeOperator::idFromCode('itsm', 'aitd', $vals['dataVolume']);
        $vals['authType'] = AttributeOperator::idFromCode('itsm', 'aita', $vals['authType']);
        $vals['status'] = AttributeOperator::idFromCode('itsm', 'aits', $vals['status']);
        $vals['publicFacing'] = (int)$vals['publicFacing'];

        $application = ApplicationDatabaseHandler::insert(ApplicationDatabaseHandler::nextNumber(), $vals['name'],
            $vals['description'], $vals['owner'], $vals['type'], $vals['status'], $vals['publicFacing'],
            $vals['lifeExpectancy'], $vals['dataVolume'], $vals['authType'], $vals['port']);

        if($vals['dataHosts'] === NULL)
            $vals['dataHosts'] = array();
        if($vals['webHosts'] === NULL)
            $vals['webHosts'] = array();
        if($vals['appHosts'] === NULL)
            $vals['appHosts'] = array();
        if($vals['vHosts'] === NULL)
            $vals['vHosts'] = array();

        ApplicationDatabaseHandler::setVHosts($application->getId(), $vals['vHosts']);
        ApplicationDatabaseHandler::setHosts($application->getId(), 'webh', $vals['webHosts']);
        ApplicationDatabaseHandler::setHosts($application->getId(), 'data', $vals['dataHosts']);
        ApplicationDatabaseHandler::setHosts($application->getId(), 'apph', $vals['appHosts']);

        $history = HistoryRecorder::writeHistory('ITSM_Application', HistoryRecorder::CREATE, $application->getId(), $application);

        $newHosts = array(
            'webHosts' => $vals['webHosts'],
            'appHosts' => $vals['appHosts'],
            'dataHosts' => $vals['dataHosts'],
            'vHosts' => $vals['vHosts']
        );

        HistoryRecorder::writeAssocHistory($history, $newHosts);

        return array('id' => $application->getNumber());
    }

    /**
     * @param Application $application
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function updateApplication(Application $application, array $vals): array
    {
        self::validate('extensions\itsm\models\Application', $vals);

        $vals['owner'] = UserOperator::idFromUsername($vals['owner']);
        $vals['type'] = AttributeOperator::idFromCode('itsm', 'aitt', $vals['type']);
        $vals['lifeExpectancy'] = AttributeOperator::idFromCode('itsm', 'aitl', $vals['lifeExpectancy']);
        $vals['dataVolume'] = AttributeOperator::idFromCode('itsm', 'aitd', $vals['dataVolume']);
        $vals['authType'] = AttributeOperator::idFromCode('itsm', 'aita', $vals['authType']);
        $vals['status'] = AttributeOperator::idFromCode('itsm', 'aits', $vals['status']);
        $vals['publicFacing'] = (int)$vals['publicFacing'];

        $history = HistoryRecorder::writeHistory('ITSM_Application', HistoryRecorder::MODIFY, $application->getId(), $application, $vals);

        if($vals['dataHosts'] === NULL)
            $vals['dataHosts'] = array();
        if($vals['webHosts'] === NULL)
            $vals['webHosts'] = array();
        if($vals['appHosts'] === NULL)
            $vals['appHosts'] = array();
        if($vals['vHosts'] === NULL)
            $vals['vHosts'] = array();

        $newHosts = array(
            'webHosts' => $vals['webHosts'],
            'appHosts' => $vals['appHosts'],
            'dataHosts' => $vals['dataHosts'],
            'vHosts' => $vals['vHosts']
        );

        HistoryRecorder::writeAssocHistory($history, $newHosts);

        ApplicationDatabaseHandler::update($application->getId(), $vals['name'],
            $vals['description'], $vals['owner'], $vals['type'], $vals['status'], $vals['publicFacing'],
            $vals['lifeExpectancy'], $vals['dataVolume'], $vals['authType'], $vals['port']);

        ApplicationDatabaseHandler::setVHosts($application->getId(), $vals['vHosts']);
        ApplicationDatabaseHandler::setHosts($application->getId(), 'webh', $vals['webHosts']);
        ApplicationDatabaseHandler::setHosts($application->getId(), 'data', $vals['dataHosts']);
        ApplicationDatabaseHandler::setHosts($application->getId(), 'apph', $vals['appHosts']);

        return array('id' => $application->getNumber());
    }

    /**
     * @param int $number
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function idFromNumber(int $number): ?int
    {
        return ApplicationDatabaseHandler::selectIdByNumber($number);
    }
}