<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 10:27 PM
 */


namespace database\itsm;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use models\itsm\Application;

class ApplicationDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Application
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Application
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id`, `number`, `name`, `description`, `owner`, `type`, `status`, `publicFacing`, `lifeExpectancy`, `dataVolume`, `authType`, `port` FROM `ITSM_Application` WHERE `id` = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("models\itsm\Application");
    }

    /**
     * @param int $number
     * @return Application
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectByNumber(int $number): Application
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `ITSM_Application` WHERE `number` = ? LIMIT 1");
        $select->bindParam(1, $number, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        return self::selectById($select->fetchColumn());
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
    public static function select(string $number = "%", string $name = "%", string $description = "%", string $ownerUsername = "%", $type = array(),
                                  $publicFacing = array(), $lifeExpectancy = array(), $dataVolume = array(), $authType = array(), string $port = "%",
                                  string $host = "%", string $vhost = "%", $status = array()): array
    {
        $query = "SELECT `ITSM_Application`.`id` FROM `ITSM_Application` WHERE `number` LIKE :number AND `name` LIKE :name AND `description` LIKE :description AND `owner` IN (SELECT `id` FROM `User` WHERE `username` LIKE :username) AND `port` LIKE :port";

        // Apply virtual host filter
        if($vhost != "%" AND $vhost != "%%")
            $query .= " AND `id` IN (SELECT `application` FROM `ITSM_Application_VHost` WHERE `vhost` IN (SELECT `id` FROM `ITSM_VHost` WHERE `domain` LIKE :vhost OR `subdomain` LIKE :vhost OR CONCAT(`subdomain`, '.', `domain`) LIKE :vhost))";

        // Apply host filter (will match ip address, system name, or asset tag)
        if($host != "%" AND $host != "%%")
            $query .= " AND `id` IN (SELECT `application` FROM `ITSM_Application_Host` WHERE `host` IN (SELECT `id` FROM `ITSM_Host` WHERE `ipAddress` LIKE :host OR `systemName` LIKE :host OR `asset` IN (SELECT `id` FROM `ITSM_Asset` WHERE `assetTag` LIKE :host)))";

        // Apply type filter
        if(is_array($type) AND !empty($type))
            $query .= " AND `status` IN (SELECT `id` FROM `Attribute` WHERE `extension` = 'itsm' AND `type` = 'aitt' AND `code` IN (" . self::getAttributeCodeString($type) . "))";

        // Apply public facing filter
        if(is_array($publicFacing) AND !empty($publicFacing))
            $query .= " AND `publicFacing` IN (" . self::getBooleanString($publicFacing) . ")";

        // Apply life expectancy filter
        if(is_array($lifeExpectancy) AND !empty($lifeExpectancy))
            $query .= " AND `lifeExpectancy` IN (SELECT `id` FROM `Attribute` WHERE `extension` = 'itsm' AND `type` = 'aitl' AND `code` IN (" . self::getAttributeCodeString($lifeExpectancy) . "))";

        // Apply data volume filter
        if(is_array($dataVolume) AND !empty($dataVolume))
            $query .= " AND `dataVolume` IN (SELECT `id` FROM `Attribute` WHERE `extension` = 'itsm' AND `type` = 'aitd' AND `code` IN (" . self::getAttributeCodeString($dataVolume) . "))";

        // Apply auth type filter
        if(is_array($authType) AND !empty($authType))
            $query .= " AND `authType` IN (SELECT `id` FROM `Attribute` WHERE `extension` = 'itsm' AND `type` = 'aita' AND `code` IN (" . self::getAttributeCodeString($authType) . "))";

        // Apply status filter
        if(is_array($status) AND !empty($status))
            $query .= " AND `status` IN (SELECT `id` FROM `Attribute` WHERE `extension` = 'itsm' AND `type` = 'aits' AND `code` IN (" . self::getAttributeCodeString($status) . "))";

        $query .= " ORDER BY `number` DESC";

        $handler = new DatabaseConnection();

        $select = $handler->prepare($query);
        $select->bindParam('number', $number, DatabaseConnection::PARAM_STR);
        $select->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $select->bindParam('description', $description, DatabaseConnection::PARAM_STR);
        $select->bindParam('username', $ownerUsername, DatabaseConnection::PARAM_STR);
        $select->bindParam('port', $port, DatabaseConnection::PARAM_STR);

        if($vhost != "%" AND $vhost != "%%")
            $select->bindParam('vhost', $vhost, DatabaseConnection::PARAM_STR);

        if($host != "%" AND $host != "%%")
            $select->bindParam('host', $host, DatabaseConnection::PARAM_STR);

        $select->execute();

        $handler->close();

        $apps = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $apps[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $apps;
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function doApplicationsReferenceHost(int $id): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `application` FROM `ITSM_Application_Host` WHERE `host` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }
}