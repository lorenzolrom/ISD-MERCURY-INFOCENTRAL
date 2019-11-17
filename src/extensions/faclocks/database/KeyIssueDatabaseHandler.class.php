<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/17/2019
 * Time: 2:46 PM
 */


namespace extensions\faclocks\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\faclocks\models\KeyIssue;

class KeyIssueDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return KeyIssue
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): KeyIssue
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `key`, `issue`, `user`, `notes` FROM `FacilitiesLock_KeyIssue` WHERE `id` = ? LIMIT 1');
        $s->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\faclocks\models\KeyIssue');
    }

    /**
     * @param int $key
     * @param int $issue
     * @param int $user
     * @param string $notes
     * @return KeyIssue
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $key, int $issue, int $user, string $notes): KeyIssue
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `FacilitiesLock_KeyIssue`(`key`, `issue`, `user`, `notes`) VALUES (:key, :issue, :user, :notes)');
        $i->bindParam('key', $key, DatabaseConnection::PARAM_INT);
        $i->bindParam('issue', $issue, DatabaseConnection::PARAM_INT);
        $i->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $i->bindParam('notes', $notes, DatabaseConnection::PARAM_STR);
        $i->execute();

        $id = $c->getLastInsertId();

        $c->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param int $user
     * @param string $notes
     * @return KeyIssue
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, int $user, string $notes): KeyIssue
    {
        $c = new DatabaseConnection();

        $u = $c->prepare('UPDATE `FacilitiesLock_KeyIssue` SET `user` = :user, `notes` = :notes WHERE `id` = :id');
        $u->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $u->bindParam('notes', $notes, DatabaseConnection::PARAM_STR);
        $u->execute();

        $c->close();
        return self::selectById($id);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $c = new DatabaseConnection();

        $d = $c->prepare('DELETE FROM `FacilitiesLock_KeyIssue` WHERE `id` = ?');
        $d->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $d->execute();

        $c->close();

        return $d->getRowCount() === 1;
    }
}