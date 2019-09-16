<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 9/15/2019
 * Time: 8:42 PM
 */


namespace database\tickets;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use models\tickets\Widget;

class WidgetDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Widget
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Widget
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id`, `user`, `workspace`, `search` FROM `Tickets_Widget` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('models\tickets\Widget');
    }

    /**
     * @param int $user
     * @param int $workspace
     * @return Widget[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectByUserWorkspace(int $user, int $workspace): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Tickets_Widget` WHERE `user` = :user AND `workspace` = :workspace');
        $select->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $select->bindParam('workspace', $workspace, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $widgets = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$widgets[] = self::selectById($id);}
            catch(EntryNotFoundException $e){}
        }

        return $widgets;
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `Tickets_Widget` WHERE `id` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $user
     * @param int $workspace
     * @param int $search
     * @return Widget
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $user, int $workspace, int $search): Widget
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `Tickets_Widget` (`user`, `workspace`, `search`) VALUES (:user, :workspace, :search)');
        $insert->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $insert->bindParam('workspace', $workspace, DatabaseConnection::PARAM_INT);
        $insert->bindParam('search', $search, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }
}