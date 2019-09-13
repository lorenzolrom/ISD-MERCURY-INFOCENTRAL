<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 9/12/2019
 * Time: 5:45 PM
 */


namespace database\tickets;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use models\tickets\Search;

class SearchDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Search
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Search
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT * FROM `Tickets_Search` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 0)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('models\tickets\Search');
    }

    /**
     * @param int $user
     * @param int $workspace
     * @param string $name
     * @return Search
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectByUserWorkspaceName(int $user, int $workspace, string $name): Search
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT * FROM `Tickets_Search` WHERE `user` = :user AND `workspace` = :workspace AND `name` = :name LIMIT 1');
        $select->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $select->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $select->bindParam('workspace', $workspace, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 0)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        return $select->fetchObject('models\tickets\Search');
    }

    /**
     * @param int $user
     * @param int $workspace
     * @return string[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectNamesByUserWorkspace(int $user, int $workspace): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `name` FROM `Tickets_Search` WHERE `user` = :user AND `workspace` = :workspace');
        $select->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $select->bindParam('workspace', $workspace, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $searches = array();

        return $select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `Tickets_Search` WHERE `id` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $user
     * @param int $workspace
     * @param string $name
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function deleteByUserWorkspaceName(int $user, int $workspace, string $name): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `Tickets_Search` WHERE `user` = :user AND `workspace` = :workspace AND `name` = :name');
        $delete->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $delete->bindParam('workspace', $workspace, DatabaseConnection::PARAM_INT);
        $delete->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $workspace
     * @param int $user
     * @param string $name
     * @param string|null $number
     * @param string|null $title
     * @param string|null $contact
     * @param string|null $assignees
     * @param string|null $severity
     * @param string|null $type
     * @param string|null $category
     * @param string|null $status
     * @param string|null $closureCode
     * @param string|null $desiredDateStart
     * @param string|null $desiredDateEnd
     * @param string|null $scheduledDateStart
     * @param string|null $scheduledDateEnd
     * @param string|null $description
     * @return Search
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $workspace, int $user, string $name, ?string $number, ?string $title,
                                  ?string $contact, ?string $assignees, ?string $severity, ?string $type,
                                  ?string $category, ?string $status, ?string $closureCode, ?string $desiredDateStart,
                                  ?string $desiredDateEnd, ?string $scheduledDateStart, ?string $scheduledDateEnd,
                                  ?string $description): Search
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `Tickets_Search` (`workspace`, `user`, `name`, `number`, `title`, 
                              `contact`, `assignees`, `severity`, `type`, `category`, `status`, `closureCode`, 
                              `desiredDateStart`, `desiredDateEnd`, `scheduledDateStart`, `scheduledDateEnd`, 
                              `description`) VALUES (:workspace, :user, :name, :number, :title, :contact, :assignees, 
                                                     :severity, :type, :category, :status, :closureCode, 
                                                     :desiredDateStart, :desiredDateEnd, :scheduledDateStart, 
                                                     :scheduledDateEnd, :description)');

        $insert->bindParam('workspace', $workspace, DatabaseConnection::PARAM_INT);
        $insert->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $insert->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $insert->bindParam('number', $number, DatabaseConnection::PARAM_STR);
        $insert->bindParam('title', $title, DatabaseConnection::PARAM_STR);
        $insert->bindParam('contact', $contact, DatabaseConnection::PARAM_STR);
        $insert->bindParam('assignees', $assignees, DatabaseConnection::PARAM_STR);
        $insert->bindParam('severity', $severity, DatabaseConnection::PARAM_STR);
        $insert->bindParam('type', $type, DatabaseConnection::PARAM_STR);
        $insert->bindParam('category', $category, DatabaseConnection::PARAM_STR);
        $insert->bindParam('status', $status, DatabaseConnection::PARAM_STR);
        $insert->bindParam('closureCode', $closureCode, DatabaseConnection::PARAM_STR);
        $insert->bindParam('desiredDateStart', $desiredDateStart, DatabaseConnection::PARAM_STR);
        $insert->bindParam('desiredDateEnd', $desiredDateEnd, DatabaseConnection::PARAM_STR);
        $insert->bindParam('scheduledDateStart', $scheduledDateStart, DatabaseConnection::PARAM_STR);
        $insert->bindParam('scheduledDateEnd', $scheduledDateEnd, DatabaseConnection::PARAM_STR);
        $insert->bindParam('description', $description, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById((int)$id);
    }

    /**
     * @param int $id
     * @param string|null $number
     * @param string|null $title
     * @param string|null $contact
     * @param string|null $assignees
     * @param string|null $severity
     * @param string|null $type
     * @param string|null $category
     * @param string|null $status
     * @param string|null $closureCode
     * @param string|null $desiredDateStart
     * @param string|null $desiredDateEnd
     * @param string|null $scheduledDateStart
     * @param string|null $scheduledDateEnd
     * @param string|null $description
     * @return Search
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, ?string $number, ?string $title,
                                  ?string $contact, ?string $assignees, ?string $severity, ?string $type,
                                  ?string $category, ?string $status, ?string $closureCode, ?string $desiredDateStart,
                                  ?string $desiredDateEnd, ?string $scheduledDateStart, ?string $scheduledDateEnd,
                                  ?string $description): Search
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `Tickets_Search` SET `number` = :number, `title` = :title, 
                            `contact` = :contact, `assignees` = :assignees, `severity` = :severity, `type` = :type, 
                            `category` = :category, `status` = :status, `closureCode` = :closureCode, 
                            `desiredDateStart` = :desiredDateStart, `desiredDateEnd` = :desiredDateEnd, 
                            `scheduledDateStart` = :scheduledDateStart, `scheduledDateEnd` = :scheduledDateEnd, 
                            `description` = :description WHERE `id` = :id');

        $update->bindParam('number', $number, DatabaseConnection::PARAM_STR);
        $update->bindParam('title', $title, DatabaseConnection::PARAM_STR);
        $update->bindParam('contact', $contact, DatabaseConnection::PARAM_STR);
        $update->bindParam('assignees', $assignees, DatabaseConnection::PARAM_STR);
        $update->bindParam('severity', $severity, DatabaseConnection::PARAM_STR);
        $update->bindParam('type', $type, DatabaseConnection::PARAM_STR);
        $update->bindParam('category', $category, DatabaseConnection::PARAM_STR);
        $update->bindParam('status', $status, DatabaseConnection::PARAM_STR);
        $update->bindParam('closureCode', $closureCode, DatabaseConnection::PARAM_STR);
        $update->bindParam('desiredDateStart', $desiredDateStart, DatabaseConnection::PARAM_STR);
        $update->bindParam('desiredDateEnd', $desiredDateEnd, DatabaseConnection::PARAM_STR);
        $update->bindParam('scheduledDateStart', $scheduledDateStart, DatabaseConnection::PARAM_STR);
        $update->bindParam('scheduledDateEnd', $scheduledDateEnd, DatabaseConnection::PARAM_STR);
        $update->bindParam('description', $description, DatabaseConnection::PARAM_STR);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }
}