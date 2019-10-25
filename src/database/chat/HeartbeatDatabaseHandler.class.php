<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 10/25/2019
 * Time: 9:01 AM
 */


namespace database\chat;


use database\DatabaseConnection;
use database\DatabaseHandler;

class HeartbeatDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $userId
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $userId): bool
    {
        $c = new DatabaseConnection();

        // Delete any existing heartbeats
        $u = $c->prepare('UPDATE `Chat_Heartbeat` SET `lastCheckIn` = NOW() WHERE `user` = ?');
        $u->bindParam(1, $userId, DatabaseConnection::PARAM_INT);
        $u->execute();

        $c->close();

        return $u->getRowCount() === 1;
    }

    /**
     * @param int $userId
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $userId): bool
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `Chat_Heartbeat` (`user`, `lastCheckIn`) VALUES (?, NOW())');
        $i->bindParam(1, $userId, DatabaseConnection::PARAM_INT);
        $i->execute();

        $c->close();

        return $i->getRowCount() === 1;
    }

    /**
     * @param int $seconds
     * @return int[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectActive(int $seconds): array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `user` FROM `Chat_Heartbeat` WHERE `lastCheckIn` > NOW() - INTERVAL ? SECOND');
        $s->bindParam(1, $seconds, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        return $s->fetchAll(DatabaseConnection::FETCH_COLUMN, 0);
    }
}