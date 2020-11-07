<?php


namespace extensions\itsm\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\itsm\models\WebServer;

class WebServerDatabaseHandler extends DatabaseHandler
{
    /**
     * @param string $ipAddress
     * @param string $systemName
     * @return WebServer[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $ipAddress = '%', string $systemName = '%'): array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `ITSM_WebServer`.*, `ITSM_Host`.`systemName`, `ITSM_Host`.`ipAddress` FROM `ITSM_WebServer` 
                                    INNER JOIN `ITSM_Host` on `ITSM_WebServer`.`host` = `ITSM_Host`.`id`
                                    WHERE `ITSM_WebServer`.host IN (SELECT `ITSM_Host`.`id` FROM `ITSM_Host` WHERE `ITSM_Host`.`ipAddress` LIKE :ipAddress AND `ITSM_Host`.`systemName` LIKE :systemName)');
        $s->bindParam('ipAddress', $ipAddress, DatabaseConnection::PARAM_STR);
        $s->bindParam('systemName', $systemName, DatabaseConnection::PARAM_STR);
        $s->execute();

        $c->close();

        return $s->fetchAll(DatabaseConnection::FETCH_CLASS, 'extensions\itsm\models\WebServer');
    }

    /**
     * @param int $host The primary ID of the Host this web server is on
     * @return WebServer
     * @throws \exceptions\DatabaseException|EntryNotFoundException
     */
    public static function selectByHost(int $host): WebServer
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `ITSM_WebServer`.*, `ITSM_Host`.`systemName`, `ITSM_Host`.`ipAddress` FROM `ITSM_WebServer` INNER JOIN `ITSM_Host` on `ITSM_WebServer`.host = `ITSM_Host`.`id` WHERE `host` = :host LIMIT 1');
        $s->bindParam('host', $host, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\itsm\models\WebServer');
    }

    /**
     * @param int $host
     * @return bool Has the Host ID already been assigned to a web server?
     * @throws \exceptions\DatabaseException
     */
    public static function isHostInUse(int $host): bool
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `host` FROM `ITSM_WebServer` WHERE `host` = :host LIMIT 1');
        $s->bindParam('host', $host, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

         return $s->getRowCount() === 1;
    }

    /**
     * @param int $host
     * @return bool Was a record deleted?
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $host): bool
    {
        $c = new DatabaseConnection();

        $d = $c->prepare('DELETE FROM `ITSM_WebServer` WHERE `host` = :host');
        $d->bindParam('host', $host, DatabaseConnection::PARAM_INT);
        $d->execute();

        $c->close();

        return $d->getRowCount() === 1;
    }

    /**
     * @param int $host
     * @param string $webroot
     * @param string $logpath
     * @return WebServer
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $host, string $webroot, string $logpath): WebServer
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `ITSM_WebServer` VALUES (:host, :webroot, :logpath)');
        $i->bindParam('host', $host, DatabaseConnection::PARAM_INT);
        $i->bindParam('webroot', $webroot, DatabaseConnection::PARAM_STR);
        $i->bindParam('logpath', $logpath, DatabaseConnection::PARAM_STR);
        $i->execute();

        $c->close();

        return self::selectByHost($host);
    }

    /**
     * @param int $host
     * @param string $webroot
     * @param string $logpath
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $host, string $webroot, string $logpath): bool
    {
        $c = new DatabaseConnection();

        $u = $c->prepare('UPDATE `ITSM_WebServer` SET `webroot` = :webroot, `logpath` = :logpath WHERE `host` = :host');
        $u->bindParam('webroot', $webroot, DatabaseConnection::PARAM_STR);
        $u->bindParam('logpath', $logpath, DatabaseConnection::PARAM_STR);
        $u->bindParam('host', $host, DatabaseConnection::PARAM_INT);
        $u->execute();

        $c->close();

        return $u->getRowCount() === 1;
    }
}