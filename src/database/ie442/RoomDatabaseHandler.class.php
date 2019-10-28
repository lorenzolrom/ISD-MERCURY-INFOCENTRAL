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


namespace database\ie442;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use models\ie442\Room;

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

        $s = $c->prepare('SELECT `id`, `user1`, `user2` FROM `442_Room` WHERE `id` = ? LIMIT 1');
        $s->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $s->execute();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        $c->close();

        return $s->fetchObject('models\ie442\Room');
    }
}