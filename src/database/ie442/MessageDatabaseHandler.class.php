<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 10/21/2019
 * Time: 4:59 PM
 */


namespace database\ie442;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use models\ie442\Message;

class MessageDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Message
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Message
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `user`, `message`, `time` FROM `442_Message` WHERE `id` = ? LIMIT 1');
        $s->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $s->execute();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        $c->close();

        return $s->fetchObject('models\ie442\Message');
    }

    /**
     * @param int|null $roomId
     * @param int|null $minutes
     * @return Message[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectByRoom(?int $roomId, int $minutes): array
    {
        $c = new DatabaseConnection();

        if($roomId === NULL)
        {
            $s = $c->prepare('SELECT `id` FROM `442_Message` WHERE `room` IS NULL AND `time` >= DATE_SUB(NOW(), INTERVAL :mins MINUTE) ORDER BY `time` ASC');
        }
        else
        {
            $s = $c->prepare('SELECT `id` FROM `442_Message` WHERE `room` = :room AND `time` >= DATE_SUB(NOW(), INTERVAL :mins MINUTE) ORDER BY `time` ASC');
            $s->bindParam('room', $roomId, DatabaseConnection::PARAM_INT);
        }

        $s->bindParam('mins', $minutes, DatabaseConnection::PARAM_INT);

        $s->execute();

        $c->close();

        $rooms = array();

        foreach($s->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try {$rooms[] = self::selectById($id);}catch(EntryNotFoundException $e){}
        }

        return $rooms;
    }

    /**
     * @param int $user
     * @param int|null $room
     * @param string $message
     * @return Message
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $user, ?int $room, string $message): Message
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `442_Message` (`user`, `room`, `message`, `time`) VALUES (:user, :room, :message, NOW())');
        $i->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $i->bindParam('room', $room, DatabaseConnection::PARAM_INT);
        $i->bindParam('message', $message, DatabaseConnection::PARAM_STR);
        $i->execute();

        $id = $c->getLastInsertId();

        $c->close();

        return self::selectById($id);
    }
}