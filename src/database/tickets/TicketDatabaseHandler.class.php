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
use exceptions\DatabaseException;
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
                                  string $contact = '%', ?array $type = array(), ?array $category = array(),
                                  ?array $status = array(), ?array $closureCode = array(), ?array $severity = array(), ?string $desiredStart = NULL,
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
        $select->bindParam('scheduleStart', $scheduledStart, DatabaseConnection::PARAM_STR);
        $select->bindParam('scheduleEnd', $scheduledEnd, DatabaseConnection::PARAM_STR);
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
     * @param string $status
     * @param int|null $closureCode
     * @param int|null $severity
     * @param string|null $desiredDate
     * @param string|null $scheduledDate
     * @return Ticket
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $workspace, int $number, string $title, ?string $contact, int $type,
                                  int $category, string $status, ?int $closureCode, ?int $severity, ?string $desiredDate,
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
        $insert->bindParam('status', $status, DatabaseConnection::PARAM_STR);
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
     * @param string $status
     * @param int|null $closureCode
     * @param int|null $severity
     * @param string|null $desiredDate
     * @param string|null $scheduledDate
     * @return Ticket
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $title, ?string $contact, int $type,
                                  int $category, string $status, ?int $closureCode, int $severity, ?string $desiredDate,
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
        $update->bindParam('status', $status, DatabaseConnection::PARAM_STR);
        $update->bindParam('closureCode', $closureCode, DatabaseConnection::PARAM_INT);
        $update->bindParam('severity', $severity, DatabaseConnection::PARAM_INT);
        $update->bindParam('desiredDate', $desiredDate, DatabaseConnection::PARAM_STR);
        $update->bindParam('scheduledDate', $scheduledDate, DatabaseConnection::PARAM_STR);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $workspace
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function nextNumber(int $workspace): int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `number` FROM `Tickets_Ticket` WHERE `workspace` = ? ORDER BY `number` DESC LIMIT 1');
        $select->bindParam(1, $workspace, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1 ? $select->fetchColumn() + 1 : 1;
    }

    /**
     * @param int $workspace
     * @param int $user
     * @return Ticket[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectByAssignee(int $workspace, int $user): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Tickets_Ticket` WHERE `workspace` = :workspace AND `status` != :closed AND `id` IN (SELECT `ticket` FROM `Tickets_Assignee` WHERE `user` = :user)');
        $select->bindParam('workspace', $workspace, DatabaseConnection::PARAM_INT);
        $select->bindParam('closed', Ticket::CLOSED, DatabaseConnection::PARAM_STR);
        $select->bindParam('user', $user, DatabaseConnection::PARAM_INT);
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
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectOpen(int $workspace): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Tickets_Ticket` WHERE `workspace` = :workspace AND `status` != :closed');
        $select->bindParam('workspace', $workspace, DatabaseConnection::PARAM_INT);
        $select->bindParam('closed', Ticket::CLOSED, DatabaseConnection::PARAM_STR);
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
     * @return array
     * @throws DatabaseException
     */
    public static function selectClosed(int $workspace): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Tickets_Ticket` WHERE `workspace` = :workspace AND `status` = :closed');
        $select->bindParam('workspace', $workspace, DatabaseConnection::PARAM_INT);
        $select->bindParam('closed', Ticket::CLOSED, DatabaseConnection::PARAM_STR);
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
     * @param int $ticket
     * @param int $team
     * @param int|null $user
     * @return bool
     * @throws DatabaseException
     */
    public static function addAssignee(int $ticket, int $team, ?int $user): bool
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `Tickets_Assignee` (`ticket`, `team`, `user`) VALUES (:ticket, :team, :user)');
        $insert->bindParam('ticket', $ticket, DatabaseConnection::PARAM_INT);
        $insert->bindParam('team', $team, DatabaseConnection::PARAM_INT);
        $insert->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $handler->close();

        return $insert->getRowCount() === 1;
    }

    /**
     * @param int $ticket
     * @param int $team
     * @param int|null $user
     * @return bool
     * @throws DatabaseException
     */
    public static function removeAssignee(int $ticket, int $team, ?int $user): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `Tickets_Assignee` WHERE `ticket` = :ticket AND `team` = :team AND `user` = :user');
        $delete->bindParam('ticket', $ticket, DatabaseConnection::PARAM_INT);
        $delete->bindParam('team', $team, DatabaseConnection::PARAM_INT);
        $delete->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $ticket
     * @param int $team
     * @return bool
     * @throws DatabaseException
     */
    public static function removeAssignedTeamOnly(int $ticket, int $team): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `Tickets_Assignee` WHERE `ticket` = :ticket AND `team` = :team AND `user` IS NULL');
        $delete->bindParam('ticket', $ticket, DatabaseConnection::PARAM_INT);
        $delete->bindParam('team', $team, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $ticket
     * @param int $team
     * @return bool
     * @throws DatabaseException
     */
    public static function removeAssignedTeam(int $ticket, int $team): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `Tickets_Assignee` WHERE `ticket` = :ticket AND `team` = :team');
        $delete->bindParam('ticket', $ticket, DatabaseConnection::PARAM_INT);
        $delete->bindParam('team', $team, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() !== 0;
    }

    /**
     * @param int $ticket
     * @return array
     * @throws DatabaseException
     */
    public static function selectAssignees(int $ticket): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `team`, `user` FROM `Tickets_Assignee` WHERE `ticket` = ?');
        $select->bindParam(1, $ticket, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->fetchAll();
    }

    /**
     * @param int $ticket
     * @param int $team
     * @return array
     * @throws DatabaseException
     */
    public static function selectAssignedTeamUsers(int $ticket, int $team): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `user` FROM `Tickets_Assignee` WHERE `ticket` = :ticket AND `team` = :team AND `user` IS NOT NULL');
        $select->bindParam('ticket', $ticket, DatabaseConnection::PARAM_INT);
        $select->bindParam('team', $team, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0);
    }

    /**
     * Is the given team/user combination assigned
     * This will not actually work for null users for some reason...
     *
     * @param int $ticket
     * @param int $team
     * @param int|null $user
     * @return bool
     * @throws DatabaseException
     */
    public static function isAssigned(int $ticket, int $team, ?int $user = NULL): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `ticket` FROM `Tickets_Assignee` WHERE `team` = :team AND `user` = :user AND `ticket` = :ticket LIMIT 1');
        $select->bindParam('team', $team, DatabaseConnection::PARAM_INT);
        $select->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $select->bindParam('ticket', $ticket, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * Is the specified team assigned in any capacity (alone or through users)?
     *
     * @param int $ticket
     * @param int $team
     * @return bool
     * @throws DatabaseException
     */
    public static function isTeamAssignedAtAll(int $ticket, int $team): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `ticket` FROM `Tickets_Assignee` WHERE `team` = :team AND `ticket` = :ticket LIMIT 1');
        $select->bindParam('team', $team, DatabaseConnection::PARAM_INT);
        $select->bindParam('ticket', $ticket, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $ticket
     * @param int $team
     * @return bool
     * @throws DatabaseException
     */
    public static function isTeamOnlyAssigned(int $ticket, int $team): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `ticket` FROM `Tickets_Assignee` WHERE `team` = :team AND `user` IS NULL AND `ticket` = :ticket LIMIT 1');
        $select->bindParam('team', $team, DatabaseConnection::PARAM_INT);
        $select->bindParam('ticket', $ticket, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * Searches ticket titles, numbers, and contacts descriptions matching the query
     *
     * @param int $workspace
     * @param string $query
     * @return Ticket[]
     * @throws DatabaseException
     */
    public static function quickSearch(int $workspace, string $query): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `Tickets_Ticket` WHERE ((`title` LIKE :query) OR (`number` LIKE :query) OR (IFNULL(`contact`, '') LIKE :query)) AND (`workspace` = :workspace)");
        $select->bindParam('query', "%$query%", DatabaseConnection::PARAM_STR);
        $select->bindParam('workspace', $workspace, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $tickets = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$tickets[] = self::selectById($id);}
            catch(EntryNotFoundException $e){} // Ignore
        }

        return $tickets;
    }

    /**
     * @param int $ticket1
     * @param int $ticket2
     * @return bool
     * @throws DatabaseException
     */
    public static function insertLink(int $ticket1, int $ticket2): bool
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `Tickets_Link` (`ticket1`, `ticket2`) VALUES (:ticket1, :ticket2)');
        $insert->bindParam('ticket1', $ticket1, DatabaseConnection::PARAM_INT);
        $insert->bindParam('ticket2', $ticket2, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $handler->close();

        return $insert->getRowCount() === 1;
    }

    /**
     * @param int $ticket1
     * @param int $ticket2
     * @return bool
     * @throws DatabaseException
     */
    public static function deleteLink(int $ticket1, int $ticket2): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `Tickets_Link` WHERE (`ticket1` = :ticket1 AND `ticket2` = :ticket2) OR (`ticket1` = :ticket2 AND `ticket2` = :ticket1)');
        $delete->bindParam('ticket1', $ticket1, DatabaseConnection::PARAM_INT);
        $delete->bindParam('ticket2', $ticket2, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount();
    }

    /**
     * @param int $ticket1
     * @param int $ticket2
     * @return bool
     * @throws DatabaseException
     */
    public static function areTicketsLinked(int $ticket1, int $ticket2): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `ticket1` FROM `Tickets_Link` WHERE (`ticket1` = :ticket1 AND `ticket2` = :ticket2) OR (`ticket1` = :ticket2 AND `ticket2` = :ticket1) LIMIT 1');
        $select->bindParam('ticket1', $ticket1, DatabaseConnection::PARAM_INT);
        $select->bindParam('ticket2', $ticket2, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $ticket
     * @return Ticket[]
     * @throws DatabaseException
     */
    public static function selectLinkedTickets(int $ticket): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `ticket1`, `ticket2` FROM `Tickets_Link` WHERE `ticket1` = :ticket OR `ticket2` = :ticket');
        $select->bindParam('ticket', $ticket, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $tickets = array();

        foreach($select->fetchAll() as $link) {

            $linkedId = NULL;

            if($link['ticket1'] == $ticket)// ticket2 is linked
            {
                $linkedId = $link['ticket2'];
            }
            else if($link['ticket2'] == $ticket) // ticket1 is linked
            {
                $linkedId = $link['ticket1'];
            }

            if($linkedId === NULL)
                continue;

            try{$tickets[] = self::selectById((int)$linkedId);}
            catch(EntryNotFoundException $e){}
        }

        return $tickets;
    }
}