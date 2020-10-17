<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 5:05 PM
 */


namespace extensions\itsm\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\itsm\models\VHost;

class VHostDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return VHost
     *
     * Selects basic information about a VHost needed for display
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     */
    public static function selectById(int $id): VHost
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id`, `domain`, `subdomain`, `name`, `host`, `registrar`, `status`, `renewCost`, `webRoot`, `logPath`, `notes`, `registerDate`, `expireDate` FROM `ITSM_VHost` WHERE `id` = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("extensions\itsm\models\VHost");
    }

    /**
     * @param string $domain
     * @param string $subdomain
     * @param string $name
     * @param string $host
     * @param string $registrarCode
     * @param mixed $status
     * @return VHost[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $domain = "%", string $subdomain = "%", string $name = "%", string $host = "%",
                                  string $registrarCode = "%", $status = array()): array
    {
        $query = "SELECT id FROM ITSM_VHost WHERE domain LIKE :domain AND subdomain LIKE :subdomain AND name LIKE :name 
                            AND (host IN (SELECT id FROM ITSM_Host WHERE asset IN (SELECT id FROM ITSM_Asset WHERE assetTag LIKE :host) OR host IN(SELECT `id` FROM `ITSM_Host` WHERE `ipAddress` LIKE :host)) 
                            AND registrar IN (SELECT id FROM ITSM_Registrar WHERE code LIKE :registrarCode))";

        // Add status filter, if it is supplied
        if(is_array($status) AND !empty($status))
        {
            $query .= " AND `status` IN (SELECT `id` FROM `Attribute` WHERE `extension` = 'itsm' AND `type` = 'wdns' AND `code` IN (" . self::getAttributeCodeString($status) . "))";
        }

        $query .= "ORDER BY `domain`, `subdomain` ASC";

        $handler = new DatabaseConnection();

        $select = $handler->prepare($query);

        $select->bindParam('domain', $domain, DatabaseConnection::PARAM_STR);
        $select->bindParam('subdomain', $subdomain, DatabaseConnection::PARAM_STR);
        $select->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $select->bindParam('registrarCode', $registrarCode, DatabaseConnection::PARAM_STR);
        $select->bindParam('host', $host, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $vhosts = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $vhosts[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){} // ignore
        }

        return $vhosts;
    }

    /**
     * @param string $domain
     * @param string $subdomain
     * @param string $name
     * @param int $host
     * @param int $registrar
     * @param int $status
     * @param float $renewCost
     * @param string $notes
     * @param string $registerDate
     * @param string $expireDate
     * @param string|null $webRoot
     * @param string|null $logPath
     * @return VHost
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $domain, string $subdomain, string $name, int $host, int $registrar,
                                  int $status, float $renewCost, string $notes, string $registerDate,
                                  ?string $expireDate, ?string $webRoot, ?string $logPath): VHost
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `ITSM_VHost` (`domain`, `subdomain`, `name`, `host`, `registrar`, `status`, `renewCost`, `notes`, `registerDate`, `expireDate`, `webRoot`, `logPath`) 
                                            VALUES (:domain, :subdomain, :name, :host, :registrar, :status, :renewCost, :notes, :registerDate, :expireDate, :webRoot, :logPath)');
        $insert->bindParam('domain', $domain, DatabaseConnection::PARAM_STR);
        $insert->bindParam('subdomain', $subdomain, DatabaseConnection::PARAM_STR);
        $insert->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $insert->bindParam('host', $host, DatabaseConnection::PARAM_INT);
        $insert->bindParam('registrar', $registrar, DatabaseConnection::PARAM_INT);
        $insert->bindParam('status', $status, DatabaseConnection::PARAM_INT);
        $insert->bindParam('renewCost', $renewCost, DatabaseConnection::PARAM_STR);
        $insert->bindParam('notes', $notes, DatabaseConnection::PARAM_STR);
        $insert->bindParam('registerDate', $registerDate, DatabaseConnection::PARAM_STR);
        $insert->bindParam('expireDate', $expireDate, DatabaseConnection::PARAM_STR);
        $insert->bindParam('webRoot', $webRoot, DatabaseConnection::PARAM_STR);
        $insert->bindParam('logPath', $logPath, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $domain
     * @param string $subdomain
     * @param string $name
     * @param int $host
     * @param int $registrar
     * @param int $status
     * @param float $renewCost
     * @param string $notes
     * @param string $registerDate
     * @param string|null $expireDate
     * @param string|null $webRoot
     * @param string|null $logPath
     * @return VHost
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $domain, string $subdomain, string $name, int $host, int $registrar,
                                  int $status, float $renewCost, string $notes, string $registerDate,
                                  ?string $expireDate, ?string $webRoot, ?string $logPath): VHost
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `ITSM_VHost` SET `domain` = :domain, `subdomain` = :subdomain, 
                        `name` = :name, `host` = :host, `registrar` = :registrar, `status` = :status, 
                        `renewCost` = :renewCost, `notes` = :notes, `registerDate` = :registerDate, 
                        `expireDate` = :expireDate, `webRoot` = :webRoot, `logPath` = :logPath WHERE `id` = :id');
        $update->bindParam('domain', $domain, DatabaseConnection::PARAM_STR);
        $update->bindParam('subdomain', $subdomain, DatabaseConnection::PARAM_STR);
        $update->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $update->bindParam('host', $host, DatabaseConnection::PARAM_INT);
        $update->bindParam('registrar', $registrar, DatabaseConnection::PARAM_INT);
        $update->bindParam('status', $status, DatabaseConnection::PARAM_INT);
        $update->bindParam('renewCost', $renewCost, DatabaseConnection::PARAM_STR);
        $update->bindParam('notes', $notes, DatabaseConnection::PARAM_STR);
        $update->bindParam('registerDate', $registerDate, DatabaseConnection::PARAM_STR);
        $update->bindParam('expireDate', $expireDate, DatabaseConnection::PARAM_STR);
        $update->bindParam('webRoot', $webRoot, DatabaseConnection::PARAM_STR);
        $update->bindParam('logPath', $logPath, DatabaseConnection::PARAM_STR);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `ITSM_VHost` WHERE `id` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function doVHostsReferenceHost(int $id): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `ITSM_VHost` WHERE `host` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $registrarId
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function doVHostsReferenceRegistrar(int $registrarId): bool
    {
        $handler = new DatabaseConnection();

        $check = $handler->prepare('SELECT `id` FROM `ITSM_VHost` WHERE `registrar` = ? LIMIT 1');
        $check->bindParam(1, $registrarId, DatabaseConnection::PARAM_INT);
        $check->execute();

        $handler->close();

        return $check->getRowCount() === 1;
    }

    /**
     * @param string $domain
     * @param string $subdomain
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function isSubdomainInUseOnDomain(string $domain, string $subdomain): bool
    {
        $handler = new DatabaseConnection();

        $check = $handler->prepare('SELECT `id` FROM `ITSM_VHost` WHERE `subdomain` = :subdomain AND `domain` = :domain LIMIT 1');
        $check->bindParam('subdomain', $subdomain, DatabaseConnection::PARAM_STR);
        $check->bindParam('domain', $domain, DatabaseConnection::PARAM_STR);
        $check->execute();

        $handler->close();

        return $check->getRowCount() === 1;
    }

//    /**
//     * @param int $id
//     * @return bool
//     * @throws \exceptions\DatabaseException
//     */
//    public static function addUser(int $id): bool
//    {
//        $handler = new DatabaseConnection();
//
//        $insert = $handler->prepare('INSERT INTO `ITSM_VHost_Manager` (`vhost`, `user`) VALUES (:vhost, :user)');
//        $insert->bindParam('vhost', $id, DatabaseConnection::PARAM_INT);
//        $insert->bindParam('user', $id, DatabaseConnection::PARAM_INT);
//        $insert->execute();
//
//        $handler->close();
//
//        return $insert->getRowCount() === 1;
//    }
//
//    /**
//     * @param int $id
//     * @param int $user
//     * @return bool
//     * @throws \exceptions\DatabaseException
//     */
//    public static function removeUser(int $id, int $user): bool
//    {
//        $handler = new DatabaseConnection();
//
//        $delete = $handler->prepare('DELETE FROM `ITSM_VHost_Manager` WHERE `vhost` = :vhost AND `user` = :user');
//        $delete->bindParam('vhost', $id, DatabaseConnection::PARAM_INT);
//        $delete->bindParam('user', $user, DatabaseConnection::PARAM_INT);
//        $delete->execute();
//
//        $handler->close();
//
//        return $delete->getRowCount();
//    }
//
//    /**
//     * @param int $id
//     * @return User[]
//     * @throws \exceptions\DatabaseException
//     */
//    public static function getUsers(int $id): array
//    {
//        $handler = new DatabaseConnection();
//
//        $select = $handler->prepare('SELECT `user` FROM `ITSM_VHost_Manager` WHERE `vhost` = ?');
//        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
//        $select->execute();
//
//        $handler->close();
//
//        $users = array();
//
//        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
//        {
//            try{$users[] = UserDatabaseHandler::selectById($id);}
//            catch(EntryNotFoundException $e){}
//        }
//
//        return $users;
//    }
//
//    /**
//     * @param int $user
//     * @return VHost[]
//     * @throws \exceptions\DatabaseException
//     */
//    public static function selectByUser(int $user): array
//    {
//        $handler = new DatabaseConnection();
//
//        $select = $handler->prepare('SELECT `vhost` FROM `ITSM_VHost_Manager` WHERE `user` = ?');
//        $select->bindParam(1, $user, DatabaseConnection::PARAM_INT);
//        $select->execute();
//
//        $handler->close();
//
//        $vhosts = array();
//
//        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
//        {
//            try{$vhosts[] = self::selectById($id);}
//            catch(EntryNotFoundException $e){}
//        }
//
//        return $vhosts;
//    }

}
