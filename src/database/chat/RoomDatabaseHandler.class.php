<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 10/21/2019
 * Time: 4:46 PM
 */


namespace database\chat;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use models\chat\Room;

class RoomDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Room
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Room
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `title`, `private`, `archived` FROM `Chat_Room` WHERE `id` = ? LIMIT 1');
        $s->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $s->execute();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        $c->close();

        return $s->fetchObject('models\chat\Room');
    }

    /**
     * @param int $private
     * @param int $archived
     * @param string $title
     * @return Room[]
     * @throws DatabaseException
     */
    public static function select(int $private, int $archived, string $title = '%'): array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare("SELECT `id`, `title`, `private`, `archived` FROM `Chat_Room` WHERE private = :private AND archived = :archived AND IFNULL(`title`, '') LIKE :title");
        $s->bindParam('private', $private, DatabaseConnection::PARAM_INT);
        $s->bindParam('archived', $archived, DatabaseConnection::PARAM_INT);
        $s->bindParam('title', $title, DatabaseConnection::PARAM_STR);
        $s->execute();

        $c->close();

        return $s->fetchAll(DatabaseConnection::FETCH_CLASS, 'models\chat\Room');
    }

    /**
     * @param int $userId
     * @return Room[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectByUser(int $userId): array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `room` FROM `Chat_Room_User` WHERE `user` = ?');
        $s->bindParam(1, $userId, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        $rooms = array();

        foreach($s->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $roomId)
        {
            try {$rooms[] = self::selectById($roomId);}catch(EntryNotFoundException $e){}
        }

        return $rooms;
    }

    /**
     * @param string $title
     * @return bool
     * @throws DatabaseException
     */
    public static function isTitleInUse(string $title): bool
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id` FROM `Chat_Room` WHERE `title` IS NOT NULL AND `title` = ? LIMIT 1');
        $s->bindParam(1, $title, DatabaseConnection::PARAM_STR);
        $s->execute();

        $c->close();

        return $s->getRowCount() === 1;
    }

    /**
     * @param int $roomId
     * @param int $userId
     * @return bool
     * @throws DatabaseException
     */
    public static function isUserInRoom(int $roomId, int $userId): bool
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `user` FROM `Chat_Room_User` WHERE `room` = :room AND `user` = :user LIMIT 1');
        $s->bindParam('room', $roomId, DatabaseConnection::PARAM_INT);
        $s->bindParam('user', $userId, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        return $s->getRowCount() === 1;
    }

    /**
     * @param int $userId
     * @param int $roomId
     * @return int
     * @throws DatabaseException
     */
    public static function selectUnreadCount(int $userId, int $roomId): int
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT COUNT(`id`) AS `unread` FROM `Chat_Message` WHERE `room` = :room AND `time` > (SELECT `time` FROM `Chat_RoomLastChecked` WHERE Chat_Message.`room` = :room AND Chat_Message.`user` = :user)');
        $s->bindParam('user', $userId, DatabaseConnection::PARAM_INT);
        $s->bindParam('room', $roomId, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        return (int)$s->fetchColumn();
    }

    /**
     * @param string|null $title
     * @param int $private
     * @param int $archived
     * @return Room
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(?string $title, int $private, int $archived): Room
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `Chat_Room` (`title`, `private`, `archived`) VALUES (:title, :private, :archived)');
        $i->bindParam('title', $title, DatabaseConnection::PARAM_STR);
        $i->bindParam('private', $private, DatabaseConnection::PARAM_INT);
        $i->bindParam('archived', $archived, DatabaseConnection::PARAM_INT);
        $i->execute();

        $id = $c->getLastInsertId();

        $c->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string|null $title
     * @param int $private
     * @param int $archived
     * @return Room
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, ?string $title, int $private, int $archived): Room
    {
        $c = new DatabaseConnection();

        $u = $c->prepare('UPDATE `Chat_Room` SET `title` = :title, `private` = :private, `archived` = :archived WHERE `id` = :id');
        $u->bindParam('title', $title, DatabaseConnection::PARAM_STR);
        $u->bindParam('private', $private, DatabaseConnection::PARAM_INT);
        $u->bindParam('archived', $archived, DatabaseConnection::PARAM_INT);
        $u->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $u->execute();

        $c->close();

        return self::selectById($id);
    }

    /**
     * @param int $roomId
     * @param int $userId
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function insertUser(int $roomId, int $userId): bool
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `Chat_Room_User` (`room`, `user`) VALUES (:room, :user)');
        $i->bindParam('room', $roomId, DatabaseConnection::PARAM_INT);
        $i->bindParam('user', $userId, DatabaseConnection::PARAM_INT);
        $i->execute();

        $c->close();

        return $i->getRowCount() === 1;
    }

    /**
     * @param int $roomId
     * @param int $userId
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function deleteUser(int $roomId, int $userId): bool
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('DELETE FROM `Chat_Room_User` WHERE `room` = :room AND `user` = :user');
        $i->bindParam('room', $roomId, DatabaseConnection::PARAM_INT);
        $i->bindParam('user', $userId, DatabaseConnection::PARAM_INT);
        $i->execute();

        $c->close();

        return $i->getRowCount() === 1;
    }

    /**
     * @param int $roomId
     * @param int $userId
     * @return bool
     * @throws DatabaseException
     */
    public static function updateUserLastCheck(int $roomId, int $userId): bool
    {
        $c = new DatabaseConnection();

        $c->startTransaction();

        try
        {
            $delExisting = $c->prepare('DELETE FROM `Chat_RoomLastChecked` WHERE `room` = :room AND `user` = :user');
            $delExisting->bindParam('room', $roomId, DatabaseConnection::PARAM_INT);
            $delExisting->bindParam('user', $userId, DatabaseConnection::PARAM_INT);
            $delExisting->execute();

            $insert = $c->prepare('INSERT INTO `Chat_RoomLastChecked`(`room`, `user`, `time`) VALUES (:room, :user, NOW())');
            $insert->bindParam('room', $roomId, DatabaseConnection::PARAM_INT);
            $insert->bindParam('user', $userId, DatabaseConnection::PARAM_INT);
            $insert->execute();

            return $insert->getRowCount() === 1;
        }
        catch (DatabaseException $e)
        {
            $c->rollback();
            $c->close();
        }

        return FALSE;
    }
}