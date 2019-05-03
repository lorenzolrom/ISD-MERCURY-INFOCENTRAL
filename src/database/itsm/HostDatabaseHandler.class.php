<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/27/2019
 * Time: 5:52 PM
 */


namespace database\itsm;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use models\itsm\Host;

class HostDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Host
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Host
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id`, `asset`, `ipAddress`, `macAddress`, `systemName`, 
                                            `systemCPU`, `systemRAM`, `systemOS`, `systemDomain` FROM `ITSM_Host` WHERE 
                                            `id` = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("models\itsm\Host");
    }

    /**
     * @param string $assetTag
     * @param string $ipAddress
     * @param string $macAddress
     * @param string $systemName
     * @param string $systemCPU
     * @param string $systemRAM
     * @param string $systemOS
     * @param string $systemDomain
     * @return Host[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $assetTag = '%', string $ipAddress = '%', string $macAddress = '%',
                                  string $systemName = '%', string $systemCPU = '%', string $systemRAM = '%',
                                  string $systemOS = '%', string $systemDomain = '%'): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `ITSM_Host` WHERE `asset` IN (SELECT `id` FROM `ITSM_Asset` 
                                        WHERE `assetTag` LIKE :assetTag) AND `ipAddress` LIKE :ipAddress AND 
                                        `macAddress` LIKE :macAddress AND `systemName` LIKE :systemName AND `systemCPU` 
                                        LIKE :systemCPU AND `systemRAM` LIKE :systemRAM AND `systemOS` LIKE :systemOS 
                                        AND `systemDomain` LIKE :systemDomain");

        $select->bindParam('assetTag', $assetTag, DatabaseConnection::PARAM_STR);
        $select->bindParam('ipAddress', $ipAddress, DatabaseConnection::PARAM_STR);
        $select->bindParam('macAddress', $macAddress, DatabaseConnection::PARAM_STR);
        $select->bindParam('systemName', $systemName, DatabaseConnection::PARAM_STR);
        $select->bindParam('systemCPU', $systemCPU, DatabaseConnection::PARAM_STR);
        $select->bindParam('systemRAM', $systemRAM, DatabaseConnection::PARAM_STR);
        $select->bindParam('systemOS', $systemOS, DatabaseConnection::PARAM_STR);
        $select->bindParam('systemDomain', $systemDomain, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $hosts = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$hosts[] = self::selectById($id);}
            catch(EntryNotFoundException $e){}
        }

        return $hosts;
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare("DELETE FROM `ITSM_Host` WHERE `id` = ?");
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param int $asset
     * @param string $ipAddress
     * @param string $macAddress
     * @param string $systemName
     * @param string $systemCPU
     * @param string $systemRAM
     * @param string $systemOS
     * @param string $systemDomain
     * @return Host
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, int $asset, string $ipAddress, string $macAddress,
                                  string $systemName, string $systemCPU, string $systemRAM, string $systemOS,
                                  string $systemDomain): Host
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare("UPDATE `ITSM_Host` SET `asset` = :asset, `ipAddress` = :ipAddress, 
                       `macAddress` = :macAddress, `systemName` = :systemName, 
                       `systemCPU` = :systemCPU, `systemRAM` = :systemRAM, `systemOS` = :systemOS, 
                       `systemDomain` = :systemDomain WHERE `id` = :id");
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->bindParam('asset', $asset, DatabaseConnection::PARAM_INT);
        $update->bindParam('ipAddress', $ipAddress, DatabaseConnection::PARAM_STR);
        $update->bindParam('macAddress', $macAddress, DatabaseConnection::PARAM_STR);
        $update->bindParam('systemName', $systemName, DatabaseConnection::PARAM_STR);
        $update->bindParam('systemCPU', $systemCPU, DatabaseConnection::PARAM_STR);
        $update->bindParam('systemRAM', $systemRAM, DatabaseConnection::PARAM_STR);
        $update->bindParam('systemOS', $systemOS, DatabaseConnection::PARAM_STR);
        $update->bindParam('systemDomain', $systemDomain, DatabaseConnection::PARAM_STR);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $asset
     * @param string $ipAddress
     * @param string $macAddress
     * @param string $systemName
     * @param string $systemCPU
     * @param string $systemRAM
     * @param string $systemOS
     * @param string $systemDomain
     * @return Host
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $asset, string $ipAddress, string $macAddress,
                                  string $systemName, string $systemCPU, string $systemRAM, string $systemOS,
                                  string $systemDomain): Host
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare("INSERT INTO `ITSM_Host` (asset, ipAddress, macAddress, systemName, 
                         systemCPU, systemRAM, systemOS, systemDomain) VALUES (:asset, :ipAddress, :macAddress, 
                         :systemName, :systemCPU, :systemRAM, :systemOS, :systemDomain)");

        $insert->bindParam('asset', $asset, DatabaseConnection::PARAM_INT);
        $insert->bindParam('ipAddress', $ipAddress, DatabaseConnection::PARAM_STR);
        $insert->bindParam('macAddress', $macAddress, DatabaseConnection::PARAM_STR);
        $insert->bindParam('systemName', $systemName, DatabaseConnection::PARAM_STR);
        $insert->bindParam('systemCPU', $systemCPU, DatabaseConnection::PARAM_STR);
        $insert->bindParam('systemRAM', $systemRAM, DatabaseConnection::PARAM_STR);
        $insert->bindParam('systemOS', $systemOS, DatabaseConnection::PARAM_STR);
        $insert->bindParam('systemDomain', $systemDomain, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param string $ipAddress
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectIdFromIPAddress(string $ipAddress): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `ITSM_Host` WHERE `ipAddress` = ? LIMIT 1');
        $select->bindParam(1, $ipAddress, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1 ? $select->fetchColumn() : NULL;
    }

    /**
     * @param string $ipAddress
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function isIPAddressInUse(string $ipAddress): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `ITSM_Host` WHERE `ipAddress` = ? LIMIT 1');
        $select->bindParam(1, $ipAddress, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param string $macAddress
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function isMACAddressInUse(string $macAddress): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `ITSM_Host` WHERE `macAddress` = ? LIMIT 1');
        $select->bindParam(1, $macAddress, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectIPAndNameById(int $id): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `ipAddress`, `systemName` FROM `ITSM_Host` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->fetch();
    }
}