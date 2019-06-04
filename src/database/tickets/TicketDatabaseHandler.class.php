<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/13/2019
 * Time: 4:46 PM
 */


namespace database\tickets;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use models\tickets\Ticket;
use utilities\Validator;

class TicketDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Ticket
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Ticket
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id`, `workspace`, `number`, `title`, `contact`, `type`, `category`, 
            `status`, `closureCode`, `severity`, `desiredDate`, `scheduledDate` FROM `Tickets_Ticket` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('models\tickets\Ticket');
    }

    /**
     * @param int $workspace
     * @param int $number
     * @return Ticket
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectByNumber(int $workspace, int $number): Ticket
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Tickets_Ticket` WHERE `workspace` = :workspace AND `number` = :number LIMIT 1');
        $select->bindParam('workspace', $workspace, DatabaseConnection::PARAM_INT);
        $select->bindParam('number', $number, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        return self::selectById($select->fetchColumn());
    }

    /**
     * @param int $workspace
     * @param string $number
     * @param string $title
     * @param string $contact
     * @param array $type
     * @param array $category
     * @param array $status
     * @param array $closureCode
     * @param array $severity
     * @param string|null $desiredStart
     * @param string|null $desiredEnd
     * @param string|null $scheduledStart
     * @param string|null $scheduledEnd
     * @return Ticket[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(int $workspace, string $number = '%', string $title = '%',
                                  string $contact = '%', array $type = array(), array $category = array(),
                                  array $status = array(), array $closureCode = array(), array $severity = array(), ?string $desiredStart = NULL,
                                  ?string $desiredEnd = NULL, ?string $scheduledStart = NULL, ?string $scheduledEnd = NULL): array
    {
        // Sanitize Dates
        if($scheduledStart === NULL OR !Validator::validDate($scheduledStart))
            $scheduledStart = '1000-01-01';
        if($desiredStart === NULL OR !Validator::validDate($desiredStart))
            $desiredStart = '1000-01-01';
        if($scheduledEnd === NULL OR !Validator::validDate($scheduledEnd))
            $scheduledEnd = '9999-12-31';
        if($desiredEnd === NULL OR !Validator::validDate($desiredEnd))
            $desiredEnd = '9999-12-31';

        $query = "SELECT `id` FROM `Tickets_Ticket` WHERE `workspace` = :workspace AND `number` LIKE :number 
                                    AND `title` LIKE :title AND IFNULL(`contact`, '') LIKE :contact AND `scheduledDate` BETWEEN :scheduleStart AND :scheduleEnd 
                                    AND `desiredDate` BETWEEN :desiredStart AND :desiredEnd";

        // Array values
        if($type !== NULL AND !empty($type))
            $query .= ' AND `type` IN (SELECT `id` FROM `Tickets_Attribute` WHERE `code` IN (' . self::getAttributeCodeString($type) . '))';
        if($category !== NULL AND !empty($category))
            $query .= ' AND `category` IN (SELECT `id` FROM `Tickets_Attribute` WHERE `code` IN (' . self::getAttributeCodeString($category) . '))';
        if($status !== NULL AND !empty($status))
            $query .= ' AND `status` IN (SELECT `id` FROM `Tickets_Attribute` WHERE `code` IN (' . self::getAttributeCodeString($status) . '))';
        if($closureCode !== NULL AND !empty($closureCode))
            $query .= ' AND `closureCode` IN (SELECT `id` FROM `Tickets_Attribute` WHERE `code` IN (' . self::getAttributeCodeString($status) . '))';
        if($severity !== NULL AND !empty($severity))
            $query .= ' AND `severity` IN (SELECT `id` FROM `Tickets_Attribute` WHERE `code` IN (' . self::getAttributeCodeString($severity) . '))';

        $handler = new DatabaseConnection();

        $select = $handler->prepare($query);
        $select->bindParam('workspace', $workspace, DatabaseConnection::PARAM_INT);
        $select->bindParam('number', $number, DatabaseConnection::PARAM_STR);
        $select->bindParam('title', $title, DatabaseConnection::PARAM_STR);
        $select->bindParam('contact', $contact, DatabaseConnection::PARAM_STR);
        $select->bindParam('scheduledStart', $scheduledStart, DatabaseConnection::PARAM_STR);
        $select->bindParam('scheduledEnd', $scheduledEnd, DatabaseConnection::PARAM_STR);
        $select->bindParam('desiredStart', $desiredStart, DatabaseConnection::PARAM_STR);
        $select->bindParam('desiredEnd', $desiredEnd, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $tickets = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$tickets[] = self::selectById($id);}
            catch(EntryNotFoundException $e){}
        }

        return $tickets;
    }

    /**
     * @param int $workspace
     * @param int $number
     * @param string $title
     * @param string|null $contact
     * @param int $type
     * @param int $category
     * @param int|null $status
     * @param int|null $closureCode
     * @param int|null $severity
     * @param string|null $desiredDate
     * @param string|null $scheduledDate
     * @return Ticket
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $workspace, int $number, string $title, ?string $contact, int $type,
                                  int $category, ?int $status, ?int $closureCode, ?int $severity, ?string $desiredDate,
                                  ?string $scheduledDate): Ticket
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `Tickets_Ticket` (`workspace`, `number`, `title`, `contact`, 
                              `type`, `category`, `status`, `closureCode`, `severity`, `desiredDate`, `scheduledDate`) VALUES 
                              (:workspace, :number, :title, :contact, :type, :category, :status, :closureCode, :severity, 
                               :desiredDate, :scheduledDate)');
        $insert->bindParam('workspace', $workspace, DatabaseConnection::PARAM_INT);
        $insert->bindParam('number', $number, DatabaseConnection::PARAM_INT);
        $insert->bindParam('title', $title, DatabaseConnection::PARAM_STR);
        $insert->bindParam('contact', $contact, DatabaseConnection::PARAM_STR);
        $insert->bindParam('type', $type, DatabaseConnection::PARAM_INT);
        $insert->bindParam('category', $category, DatabaseConnection::PARAM_INT);
        $insert->bindParam('status', $status, DatabaseConnection::PARAM_INT);
        $insert->bindParam('closureCode', $closureCode, DatabaseConnection::PARAM_INT);
        $insert->bindParam('severity', $severity, DatabaseConnection::PARAM_INT);
        $insert->bindParam('desiredDate', $desiredDate, DatabaseConnection::PARAM_STR);
        $insert->bindParam('scheduledDate', $scheduledDate, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $title
     * @param string|null $contact
     * @param int $type
     * @param int $category
     * @param int|null $status
     * @param int|null $closureCode
     * @param int|null $severity
     * @param string|null $desiredDate
     * @param string|null $scheduledDate
     * @return Ticket
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $title, ?string $contact, int $type,
                                  int $category, ?int $status, ?int $closureCode, ?int $severity, ?string $desiredDate,
                                  ?string $scheduledDate): Ticket
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `Tickets_Ticket` SET `title` = :title, `contact` = :contact, 
                            `type` = :type, `category` = :category, `status` = :status, `closureCode` = :closureCode, `severity` = :severity, 
                            `desiredDate` = :desiredDate, `scheduledDate` = :scheduledDate WHERE `id` = :id');
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->bindParam('title', $title, DatabaseConnection::PARAM_STR);
        $update->bindParam('contact', $contact, DatabaseConnection::PARAM_STR);
        $update->bindParam('type', $type, DatabaseConnection::PARAM_INT);
        $update->bindParam('category', $category, DatabaseConnection::PARAM_INT);
        $update->bindParam('status', $status, DatabaseConnection::PARAM_INT);
        $update->bindParam('closureCode', $closureCode, DatabaseConnection::PARAM_INT);
        $update->bindParam('severity', $severity, DatabaseConnection::PARAM_INT);
        $update->bindParam('desiredDate', $desiredDate, DatabaseConnection::PARAM_STR);
        $update->bindParam('scheduledDate', $scheduledDate, DatabaseConnection::PARAM_STR);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }
}