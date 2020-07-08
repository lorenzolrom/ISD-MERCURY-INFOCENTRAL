<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 12/06/2019
 * Time: 10:35 AM
 */


namespace extensions\tickets\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use extensions\tickets\ExtConfig;
use extensions\tickets\models\Lock;

class LockDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $ticket
     * @param int $user
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $ticket, int $user): bool
    {
        $c = new DatabaseConnection();
        $c->startTransaction();

        $d = $c->prepare('DELETE FROM `Tickets_Lock` WHERE `ticket` = ?');
        $d->bindParam(1, $ticket, DatabaseConnection::PARAM_INT);
        $d->execute();

        $i = $c->prepare('INSERT INTO `Tickets_Lock`(`ticket`, `user`, `lastCheckin`) VALUES (:ticket, :user, NOW())');
        $i->bindParam('ticket', $ticket, DatabaseConnection::PARAM_INT);
        $i->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $i->execute();

        $c->commit();
        $c->close();

        return $i->getRowCount() === 1;
    }

    /**
     * Delete all locks for the supplied ticket
     *
     * @param int $ticket
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $ticket): bool
    {
        $c = new DatabaseConnection();

        $d = $c->prepare('DELETE FROM `Tickets_Lock` WHERE `ticket` = ?');
        $d->bindParam(1, $ticket, DatabaseConnection::PARAM_INT);
        $d->execute();

        $c->close();

        return $d->getRowCount() > 0;
    }

    /**
     * @param int $ticket
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $ticket): bool
    {
        $c = new DatabaseConnection();

        $u = $c->prepare('UPDATE `Tickets_Lock` SET `lastCheckin` = NOW() WHERE `ticket` = :ticket');
        $u->bindParam('ticket', $ticket, DatabaseConnection::PARAM_INT);
        $u->execute();

        $c->close();

        return $u->getRowCount() === 1;
    }

    /**
     * @param int $ticket
     * @return Lock|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectActive(int $ticket): ?Lock
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `ticket`, `user`, `lastCheckin` FROM `Tickets_Lock` WHERE `ticket` = :ticket AND `lastCheckin` >= DATE_SUB(NOW(), INTERVAL :seconds SECOND) ORDER BY `lastCheckin` DESC LIMIT 1');
        $s->bindParam('ticket', $ticket, DatabaseConnection::PARAM_INT);
        $s->bindParam('seconds', (int)ExtConfig::OPTIONS['lockTimeoutSeconds'], DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            return NULL;

        return $s->fetchObject('extensions\tickets\models\Lock');
    }
}
