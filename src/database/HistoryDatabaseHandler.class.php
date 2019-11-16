<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/26/2019
 * Time: 10:50 AM
 */


namespace database;


use exceptions\EntryNotFoundException;
use models\History;

class HistoryDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return History
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): History
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id`, `action`, `table`, `index`, `username`, `time` FROM `History` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('models\History');
    }

    /**
     * @param string $table
     * @param string $index
     * @param string $action
     * @param string $username
     * @return History[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $table, string $index = '%', string $action = '%', string $username = '%'): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `History` WHERE `table` LIKE :table AND `index` LIKE :index AND `action` LIKE :action AND IFNULL(`username`, \'\') LIKE :username');
        $select->bindParam('table', $table, DatabaseConnection::PARAM_STR);
        $select->bindParam('index', $index, DatabaseConnection::PARAM_STR);
        $select->bindParam('action', $action, DatabaseConnection::PARAM_STR);
        $select->bindParam('username', $username, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $histories = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $histories[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $histories;
    }

    /**
     * @param string $table
     * @param string $action
     * @param string $index
     * @param string|null $username
     * @param string $time
     * @return History
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $table, string $action, string $index, ?string $username, string $time): History
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `History` (`table`, `action`, `index`, `username`, `time`) VALUES (:table, :action, :index, :username, :time)');
        $insert->bindParam('table', $table, DatabaseConnection::PARAM_STR);
        $insert->bindParam('action', $action, DatabaseConnection::PARAM_STR);
        $insert->bindParam('index', $index, DatabaseConnection::PARAM_STR);
        $insert->bindParam('username', $username, DatabaseConnection::PARAM_STR);
        $insert->bindParam('time', $time, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $history
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectHistoryItemsByHistory(int $history): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `column`, `oldValue`, `newValue` FROM `HistoryItem` WHERE `history` = ?');
        $select->bindParam(1, $history, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->fetchAll();
    }

    /**
     * @param int $history
     * @param string $column
     * @param string $oldValue
     * @param string $newValue
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function insertHistoryItem(int $history, string $column, ?string $oldValue, ?string $newValue): bool
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `HistoryItem` (`history`, `column`, `oldValue`, `newValue`) VALUES (:history, :column, :oldValue, :newValue)');
        $insert->bindParam('history', $history, DatabaseConnection::PARAM_INT);
        $insert->bindParam('column', $column, DatabaseConnection::PARAM_STR);
        $insert->bindParam('oldValue', $oldValue, DatabaseConnection::PARAM_STR);
        $insert->bindParam('newValue', $newValue, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $handler->close();

        return TRUE;
    }
}