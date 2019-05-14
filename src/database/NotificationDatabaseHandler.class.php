<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 10:45 PM
 */


namespace database;


use exceptions\EntryNotFoundException;
use models\Notification;

class NotificationDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @param bool $preventDeleted
     * @return Notification
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id, bool $preventDeleted = FALSE): Notification
    {
        $query = "SELECT `id`, `user`, `title`, `data`, `read`, `deleted`, `important`, `time` FROM `Notification` WHERE id = ?" . ($preventDeleted ? " AND `deleted` = 0" : "") . " LIMIT 1";

        $handler = new DatabaseConnection();

        $select = $handler->prepare($query);
        $select->bindParam(1,$id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("models\Notification");
    }

    /**
     * @param int $userId
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function selectUnreadCountByUser(int $userId): int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT COUNT(`id`) FROM `Notification` WHERE `user` = ? AND `read` = 0 AND `deleted` = 0");
        $select->bindParam(1, $userId, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->fetchColumn();
    }

    /**
     * @param int $user
     * @param array $read
     * @param array $deleted
     * @param array $important
     * @return Notification[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectByUser(int $user, $read = array(), $deleted = array(), $important = array()): array
    {
        $query = 'SELECT `id` FROM `Notification` WHERE user = ?';

        // Apply read filter
        if(is_array($read) AND !empty($read))
            $query .= " AND `read` IN (" . self::getBooleanString($read) . ")";

        // Apply deleted filter
        if(is_array($deleted) AND !empty($deleted))
            $query .= " AND `deleted` IN (" . self::getBooleanString($deleted) . ")";

        // Apply important filter
        if(is_array($important) AND !empty($important))
            $query .= " AND `important` IN (" . self::getBooleanString($important) . ")";

        $handler = new DatabaseConnection();

        $select = $handler->prepare($query);
        $select->bindParam(1, $user, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $notifications = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $notifications[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $notifications;
    }

    /**
     * @param int $id
     * @param int $read
     * @param int $deleted
     * @return Notification
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, int $read, int $deleted): Notification
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare("UPDATE `Notification` SET `read` = :read, `deleted` = :deleted WHERE `id` = :id");
        $update->bindParam('read', $read, DatabaseConnection::PARAM_INT);
        $update->bindParam('deleted', $deleted, DatabaseConnection::PARAM_INT);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $user
     * @param string $title
     * @param string $data
     * @param int $important
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $user, string $title, string$data, int $important): bool
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `Notification` (`user`, `title`, `data`, `important`, `time`) 
                  VALUES (:user, :title, :data, :important, NOW())');
        $insert->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $insert->bindParam('title', $title, DatabaseConnection::PARAM_STR);
        $insert->bindParam('data', $data, DatabaseConnection::PARAM_STR);
        $insert->bindParam('important', $important, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $handler->close();

        return $insert->getRowCount() === 1;
    }
}