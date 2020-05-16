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


namespace extensions\itsm\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use extensions\itsm\models\Application;
use extensions\itsm\models\Host;
use extensions\itsm\models\VHost;

class ApplicationDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Application
     * @throws EntryNotFoundException
     * @throws DatabaseException
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

        return $select->fetchObject("extensions\itsm\models\Application");
    }

    /**
     * @param int $number
     * @return Application
     * @throws EntryNotFoundException
     * @throws DatabaseException
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
     * @param int $number
     * @return int|null
     * @throws DatabaseException
     */
    public static function selectIdByNumber(int $number): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `ITSM_Application` WHERE `number` = ? LIMIT 1");
        $select->bindParam(1, $number, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1 ? $select->fetchColumn() : NULL;
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
     * @throws DatabaseException
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
            $query .= " AND `type` IN (SELECT `id` FROM `Attribute` WHERE `extension` = 'itsm' AND `type` = 'aitt' AND `code` IN (" . self::getAttributeCodeString($type) . "))";

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
     * @throws DatabaseException
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

    /**
     * @param int $id
     * @return bool
     * @throws DatabaseException
     */
    public static function doApplicationsReferenceVHost(int $id): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `application` FROM `ITSM_Application_VHost` WHERE `vhost` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $number
     * @param string $name
     * @param string $description
     * @param int $owner
     * @param int $type
     * @param int $status
     * @param int $publicFacing
     * @param int $lifeExpectancy
     * @param int $dataVolume
     * @param int $authType
     * @param string $port
     * @return Application
     * @throws EntryNotFoundException
     * @throws DatabaseException
     */
    public static function insert(int $number, string $name, string $description, int $owner, int $type, int $status,
                                  int $publicFacing, int $lifeExpectancy, int $dataVolume, int $authType,
                                  string $port): Application
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `ITSM_Application` (`number`, `name`, `description`, `owner`, `type`, 
                                `status`, `publicFacing`, `lifeExpectancy`, `dataVolume`, `authType`, `port`) VALUES (:number, 
                                :name, :description, :owner, :type, :status, :publicFacing, :lifeExpectancy, 
                                :dateVolume, :authType, :port)');

        $insert->bindParam('number', $number, DatabaseConnection::PARAM_INT);
        $insert->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $insert->bindParam('description', $description, DatabaseConnection::PARAM_STR);
        $insert->bindParam('owner', $owner, DatabaseConnection::PARAM_INT);
        $insert->bindParam('type', $type, DatabaseConnection::PARAM_INT);
        $insert->bindParam('status', $status, DatabaseConnection::PARAM_INT);
        $insert->bindParam('publicFacing', $publicFacing, DatabaseConnection::PARAM_INT);
        $insert->bindParam('lifeExpectancy', $lifeExpectancy, DatabaseConnection::PARAM_INT);
        $insert->bindParam('dateVolume', $dataVolume, DatabaseConnection::PARAM_INT);
        $insert->bindParam('authType', $authType, DatabaseConnection::PARAM_INT);
        $insert->bindParam('port', $port, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $name
     * @param string $description
     * @param int $owner
     * @param int $type
     * @param int $status
     * @param int $publicFacing
     * @param int $lifeExpectancy
     * @param int $dataVolume
     * @param int $authType
     * @param string $port
     * @return Application
     * @throws EntryNotFoundException
     * @throws DatabaseException
     */
    public static function update(int $id, string $name, string $description, int $owner, int $type, int $status,
                                  int $publicFacing, int $lifeExpectancy, int $dataVolume, int $authType,
                                  string $port): Application
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `ITSM_Application` SET `name` = :name, `description` = :description, 
                              `owner` = :owner, `type` = :type, `status` = :status, `publicFacing` = :publicFacing, 
                              `lifeExpectancy` = :lifeExpectancy, `dataVolume` = :dataVolume, `authType` = :authType, 
                              `port` = :port WHERE `id` = :id');

        $update->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $update->bindParam('description', $description, DatabaseConnection::PARAM_STR);
        $update->bindParam('owner', $owner, DatabaseConnection::PARAM_INT);
        $update->bindParam('type', $type, DatabaseConnection::PARAM_INT);
        $update->bindParam('status', $status, DatabaseConnection::PARAM_INT);
        $update->bindParam('publicFacing', $publicFacing, DatabaseConnection::PARAM_INT);
        $update->bindParam('lifeExpectancy', $lifeExpectancy, DatabaseConnection::PARAM_INT);
        $update->bindParam('dataVolume', $dataVolume, DatabaseConnection::PARAM_INT);
        $update->bindParam('authType', $authType, DatabaseConnection::PARAM_INT);
        $update->bindParam('port', $port, DatabaseConnection::PARAM_STR);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @return bool
     * @throws DatabaseException
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `ITSM_Application` WHERE `id` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param string $type
     * @return Host[]
     * @throws DatabaseException
     */
    public static function getHosts(int $id, string $type): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `host` FROM `ITSM_Application_Host` WHERE `application` = :application AND `relationship` = :relationship');
        $select->bindParam('application', $id, DatabaseConnection::PARAM_INT);
        $select->bindParam('relationship', $type, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $hosts = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$hosts[] = HostDatabaseHandler::selectById($id);}
            catch(EntryNotFoundException $e){}
        }

        return $hosts;
    }

    /**
     * @param int $id
     * @return VHost[]
     * @throws DatabaseException
     */
    public static function getVHosts(int $id): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `vhost` FROM `ITSM_Application_VHost` WHERE `application` = ?');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $vhosts = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$vhosts[] = VHostDatabaseHandler::selectById($id);}
            catch(EntryNotFoundException $e){}
        }

        return $vhosts;
    }

    /**
     * @return int
     * @throws DatabaseException
     */
    public static function nextNumber(): int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `number` FROM `ITSM_Application` ORDER BY `number` DESC LIMIT 1');
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 0 ? 1 : $select->fetchColumn() + 1;
    }

    /**
     * @param int $id
     * @param string $type
     * @param array $hostIds
     * @return int
     * @throws DatabaseException
     */
    public static function setHosts(int $id, string $type, array $hostIds): int
    {
        $handler = new DatabaseConnection();

        // Remove existing hosts
        $delete = $handler->prepare('DELETE FROM `ITSM_Application_Host` WHERE `application` = :application AND `relationship` = :type');
        $delete->bindParam('application', $id, DatabaseConnection::PARAM_INT);
        $delete->bindParam('type', $type, DatabaseConnection::PARAM_STR);
        $delete->execute();

        $insert = $handler->prepare('INSERT INTO `ITSM_Application_Host` (application, host, relationship) VALUES (:application, :host, :relationship)');
        $insert->bindParam('application', $id, DatabaseConnection::PARAM_INT);
        $insert->bindParam('relationship', $type, DatabaseConnection::PARAM_STR);

        // Add all hosts
        $added = 0;

        foreach($hostIds as $hostId)
        {
            // Validate id is the right format
            if(!ctype_digit($hostId))
                continue;

            try
            {
                $insert->bindParam('host', $hostId, DatabaseConnection::PARAM_INT);
                $insert->execute();

                $added += 1;
            }
            catch(DatabaseException $e){} // ignore invalid hosts
        }

        $handler->close();

        return $added;
    }

    /**
     * @param int $id
     * @param array $vhostIds
     * @return int
     * @throws DatabaseException
     */
    public static function setVHosts(int $id, array $vhostIds): int
    {
        $handler = new DatabaseConnection();

        // Remove vhosts
        $delete = $handler->prepare('DELETE FROM `ITSM_Application_VHost` WHERE `application` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        // Add vhosts
        $added = 0;

        $insert = $handler->prepare('INSERT INTO `ITSM_Application_VHost` (application, vhost) VALUES (:application, :vhost)');
        $insert->bindParam('application', $id, DatabaseConnection::PARAM_INT);

        foreach($vhostIds as $vhostId)
        {
            if(!ctype_digit($vhostId))
                continue;

            try
            {
                $insert->bindParam('vhost', $vhostId, DatabaseConnection::PARAM_INT);
                $insert->execute();
                $added += 1;
            }
            catch(DatabaseException $e){}
        }

        $handler->close();

        return $added;
    }
}
