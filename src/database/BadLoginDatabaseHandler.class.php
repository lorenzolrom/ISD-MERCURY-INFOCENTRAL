<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/21/2019
 * Time: 10:24 AM
 */


namespace database;


class BadLoginDatabaseHandler extends DatabaseHandler
{
    /**
     * @param string $timeStart
     * @param string $timeEnd
     * @param string $usernameFilter
     * @param string $ipFilter
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $timeStart = '1000-01-01 00:00:00', string $timeEnd = '9999-12-31 23:59:59', string $usernameFilter = '%', string $ipFilter = '%'): array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `time`, `username`, `suppliedIP`, `sourceIP` FROM `BadLogin` WHERE `username` LIKE :username
                  AND (`sourceIP` LIKE :ipFilter OR `suppliedIP` LIKE :ipFilter) AND `time` BETWEEN :timeStart AND :timeEnd ORDER BY `time` DESC');
        $s->bindParam('username', $usernameFilter, DatabaseConnection::PARAM_STR);
        $s->bindParam('ipFilter', $ipFilter, DatabaseConnection::PARAM_STR);
        $s->bindParam('timeStart', $timeStart, DatabaseConnection::PARAM_STR);
        $s->bindParam('timeEnd', $timeEnd, DatabaseConnection::PARAM_STR);
        $s->execute();

        $c->close();

        return $s->fetchAll();
    }

    /**
     * @param string $username
     * @param string $suppliedIP
     * @param string $sourceIP
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $username, string $suppliedIP, string $sourceIP): bool
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `BadLogin`(`time`, `username`, `suppliedIP`, `sourceIP`) VALUES (NOW(), :username, :suppliedIP, :sourceIP)');
        $i->bindParam('username', $username, DatabaseConnection::PARAM_STR);
        $i->bindParam('suppliedIP', $suppliedIP, DatabaseConnection::PARAM_STR);
        $i->bindParam('sourceIP', $sourceIP, DatabaseConnection::PARAM_STR);
        $i->execute();

        $c->close();

        return $i->getRowCount() === 1;
    }
}