<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/03/2019
 * Time: 5:52 PM
 */


namespace database\itsm;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use models\itsm\ApplicationUpdate;

class ApplicationUpdateDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return ApplicationUpdate
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): ApplicationUpdate
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id`, `application`, `status`, `time`, `user`, `description` FROM `ITSM_ApplicationUpdate` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('models\itsm\ApplicationUpdate');
    }

    /**
     * @param int $appId
     * @param int|null $limit
     * @return ApplicationUpdate[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectByApplication(int $appId, ?int $limit = NULL): array
    {
        $query = 'SELECT `id` FROM `ITSM_ApplicationUpdate` WHERE `application` = ? ORDER BY `time` DESC';

        if($limit !== NULL)
            $query .= " LIMIT $limit";

        $handler = new DatabaseConnection();

        $select = $handler->prepare($query);
        $select->bindParam(1, $appId, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $updates = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$updates[] = self::selectById($id);}
            catch(EntryNotFoundException $e){}
        }

        return $updates;
    }

    /**
     * @param int $application
     * @param int $status
     * @param int $user
     * @param int $description
     * @return ApplicationUpdate
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $application, int $status, int $user, int $description): ApplicationUpdate
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `ITSM_ApplicationUpdate` (application, status, time, user, description) VALUES (:application, :status, NOW(), :user, :description)');
        $insert->bindParam('application', $application, DatabaseConnection::PARAM_INT);
        $insert->bindParam('status', $status, DatabaseConnection::PARAM_INT);
        $insert->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $insert->bindParam('description', $description, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }
}