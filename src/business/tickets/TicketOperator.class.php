<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/13/2019
 * Time: 6:49 PM
 */


namespace business\tickets;


use business\HistoryOperator;
use business\Operator;
use business\UserOperator;
use controllers\CurrentUserController;
use database\tickets\AttributeDatabaseHandler;
use database\tickets\TicketDatabaseHandler;
use database\tickets\UpdateDatabaseHandler;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use models\tickets\Ticket;
use models\tickets\Workspace;
use utilities\HistoryRecorder;
use utilities\Validator;

class TicketOperator extends Operator
{
    public const FIELDS = array('title', 'contact', 'type', 'category', 'status', 'closureCode', 'severity', 'desiredDate', 'scheduledDate', 'description');
    public const SEARCH_FIELDS = array('title', 'number', 'contact', 'type', 'category', 'status', 'closureCode', 'severity', 'desiredStart', 'desiredEnd', 'scheduledStart', 'scheduledEnd');

    private const FIELD_NAMES = array(
        'title' => 'Title',
        'contact' => 'Contact',
        'type' => 'Type',
        'category' => 'Category',
        'status' => 'Status',
        'closureCode' => 'Closure Code',
        'severity' => 'Severity',
        'desiredDate' => 'Desired Date',
        'scheduledDate' => 'Scheduled Date'
    );

    private const ATTR_FIELDS = array(
        'type',
        'category',
        'closureCode',
        'severity'
    );

    /**
     * @param Workspace $workspace
     * @param int $number
     * @return Ticket
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getTicket(Workspace $workspace, int $number): Ticket
    {
        return TicketDatabaseHandler::selectByNumber($workspace->getId(), $number);
    }

    /**
     * @param Workspace $workspace
     * @param array $vals
     * @return Ticket[]
     * @throws \exceptions\DatabaseException
     */
    public static function getSearchResults(Workspace $workspace, array $vals): array
    {
        // These values must be arrays or null
        foreach(array('type', 'category', 'status', 'closureCode', 'severity') as $val)
        {
            if($vals[$val] !== NULL AND !is_array($vals[$val]))
                $vals[$val] = NULL;
        }

        // Format dates
        foreach(array('desiredStart', 'desiredEnd', 'scheduledStart', 'scheduledEnd') as $val)
        {
            if($vals[$val] === NULL)
                continue;

            trim($vals[$val], '%'); // Remove wildcards

            if(!Validator::validDate($vals[$val])) // Convert invalid dates
            {
                if(strpos($val, 'Start'))
                    $vals[$val] = '1000-01-01';
                else
                    $vals[$val] = '9999-12-31';
            }
        }

        return TicketDatabaseHandler::select($workspace->getId(), $vals['number'], $vals['title'], $vals['contact'], $vals['type'], $vals['category'], $vals['status'], $vals['closureCode'], $vals['severity'],
            $vals['desiredStart'], $vals['desiredEnd'], $vals['scheduledStart'], $vals['scheduledEnd']);
    }

    /**
     * @param Workspace $workspace
     * @param array $vals
     * @param bool $ignoreSeverity
     * @return Ticket
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function createTicket(Workspace $workspace, array $vals, bool $ignoreSeverity = FALSE): Ticket
    {
        self::validateTicket($workspace, $vals, $ignoreSeverity);

        // Update required for new ticket
        if($vals['description'] === NULL OR strlen($vals['description']) == 0)
            throw new ValidationError(array('Initial description is required'));

        // Convert
        $vals = self::formatValues($workspace, $vals);

        $ticket = TicketDatabaseHandler::insert($workspace->getId(), TicketDatabaseHandler::nextNumber($workspace->getId()), $vals['title'], $vals['contact'],
            $vals['type'], $vals['category'], $vals['status'], $vals['closureCode'], $vals['severity'], $vals['desiredDate'], $vals['scheduledDate']);

        $history = HistoryRecorder::writeHistory('Tickets_Ticket', HistoryRecorder::CREATE, $ticket->getId(), $ticket);

        // Create update
        UpdateDatabaseHandler::insert($ticket->getId(), CurrentUserController::currentUser()->getId(), $vals['description']);
        HistoryRecorder::writeAssocHistory($history, array('Appended Description' => array(''))); // blank description, it is held in TicketUpdate
        
        return $ticket;
    }

    /**
     * @param Ticket $ticket
     * @param array $vals
     * @return Ticket
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function updateTicket(Ticket $ticket, array $vals): Ticket
    {
        $workspace = WorkspaceOperator::getWorkspace($ticket->getWorkspace());

        self::validateTicket($workspace, $vals);

        $vals = self::formatValues($workspace, $vals);

        $history = HistoryRecorder::writeHistory('Tickets_Ticket', HistoryRecorder::MODIFY, $ticket->getId(), $ticket, $vals, array('closureCode', 'contact', 'desiredDate', 'scheduledDate'));
        $ticket = TicketDatabaseHandler::update($ticket->getId(), $vals['title'], $vals['contact'], $vals['type'], $vals['category'], $vals['status'], $vals['closureCode'],
            $vals['severity'], $vals['desiredDate'], $vals['scheduledDate']);

        if($vals['description'] !== NULL AND strlen($vals['description']) !== 0)
        {
            // Create update
            UpdateDatabaseHandler::insert($ticket->getId(), CurrentUserController::currentUser()->getId(), $vals['description']);
            HistoryRecorder::writeAssocHistory($history, array('systemEntry' => array('Appended Description')));
        }

        return $ticket;
    }

    /**
     * @param array $vals
     * @return Ticket
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function createRequest(array $vals): Ticket
    {
        // Determine customer request workspace
        $workspace = WorkspaceOperator::getRequestPortal();

        $vals['status'] = Ticket::NEW; // New
        $vals['severity'] = NULL;

        return self::createTicket($workspace, $vals, TRUE);
    }

    /**
     * @param Ticket $ticket
     * @param string|null $description
     * @return Ticket
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function updateRequest(Ticket $ticket, ?string $description): Ticket
    {
        if($description === NULL OR strlen($description) === 0)
            throw new ValidationError(array('Description is required'));

        if($ticket->getStatus() == Ticket::CLOSED) // If ticket is closed
            $vals['status'] = Ticket::REOPENED; // Re-Opened
        else
            $vals['status'] = Ticket::RESPONDED; // Customer responded

        $history = HistoryRecorder::writeHistory('Tickets_Ticket', HistoryRecorder::MODIFY, $ticket->getId(), $ticket, $vals, array('severity', 'desiredDate', 'scheduledDate'));
        $ticket = TicketDatabaseHandler::update($ticket->getId(), $ticket->getTitle(), $ticket->getContact(), $ticket->getType(), $ticket->getCategory(), 'res', $ticket->getClosureCode(), $ticket->getSeverity(), $ticket->getDesiredDate(), $ticket->getScheduledDate());

        UpdateDatabaseHandler::insert($ticket->getId(), CurrentUserController::currentUser()->getId(), $description);
        HistoryRecorder::writeAssocHistory($history, array('systemEntry' => array('Appended Description')));

        return $ticket;
    }

    /**
     * @param Workspace $workspace
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function getMyAssignments(Workspace $workspace): array
    {
        return TicketDatabaseHandler::selectByAssignee($workspace->getId(), CurrentUserController::currentUser()->getId());
    }

    /**
     * @param Workspace $workspace
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function getOpenTickets(Workspace $workspace): array
    {
        return TicketDatabaseHandler::selectOpen($workspace->getId());
    }

    /**
     * @param Workspace $workspace
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function getClosedTickets(Workspace $workspace): array
    {
        return TicketDatabaseHandler::selectClosed($workspace->getId());
    }

    /**
     * @param Ticket $ticket
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function getTicketHistory(Ticket $ticket): array
    {
        $history = HistoryOperator::getHistory('ticket', $ticket->getId());
        $data = array();

        foreach($history as $item)
        {
            $itemData = array();

            $itemData['user'] = $item->getUsername();
            $itemData['time'] = $item->getTime();

            $columns = $item->getItems();
            $columnStrings = array();

            foreach($columns as $column)
            {
                if($column['column'] == 'systemEntry') // This gets passed right through
                {
                    $columnStrings[] = $column['newValue'];
                }
                else if(in_array($column['column'], array_keys(self::FIELD_NAMES))) // Column has a specified display name
                {
                    $string = self::FIELD_NAMES[$column['column']] . " ";

                    $oldVal = $column['oldValue'];
                    $newVal = $column['newValue'];

                    if(in_array($column['column'], self::ATTR_FIELDS)) // Check if field is an attribute ID that must be converted to a display name
                    {
                        $oldVal = AttributeOperator::nameFromId((int)$oldVal);
                        $newVal = AttributeOperator::nameFromId((int)$newVal);
                    }
                    else if($column['column'] == 'status') // special case for status
                    {
                        // If code is not three characters, it is not a built-in status code
                        if(strlen($oldVal) === 4)
                        {
                            try{$oldVal = AttributeOperator::getByCode(WorkspaceOperator::getWorkspace($ticket->getWorkspace()), 'status', $oldVal)->getName();}
                            catch(EntryNotFoundException $e){}
                        }
                        else
                            $oldVal = Ticket::STATIC_STATUSES[$oldVal];


                        if(strlen($newVal) === 4)
                        {
                            try{$newVal = AttributeOperator::getByCode(WorkspaceOperator::getWorkspace($ticket->getWorkspace()), 'status', $newVal)->getName();}
                            catch(EntryNotFoundException $e){}
                        }
                        else
                            $newVal = Ticket::STATIC_STATUSES[$newVal];
                    }

                    if($oldVal === NULL OR strlen($oldVal) === 0 OR $oldVal == $newVal) // If value was not set, don't include blank old one
                    {
                        $string .= "set ";
                    }
                    else
                    {
                        $string .= "changed from '$oldVal' ";
                    }

                    $string .= "to '$newVal'";
                    $columnStrings[] = $string;
                }
            }

            $itemData['changes'] = $columnStrings;
            $data[] = $itemData;
        }

        return $data;
    }

    /**
     * @param Ticket $ticket
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function getTicketAssignees(Ticket $ticket): array
    {
        return TicketDatabaseHandler::selectAssignees($ticket->getId());
    }

    /**
     * @param Workspace $workspace
     * @param array $vals
     * @param bool $ignoreSeverity
     * @return bool
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     */
    private static function validateTicket(Workspace $workspace, array $vals, bool $ignoreSeverity = FALSE): bool
    {
        $errs = array();

        if($vals['title'] === NULL OR strlen($vals['title']) === 0)
            $errs[] = 'Title is required';

        // Contact valid or empty
        if(strlen($vals['contact']) !== 0 AND UserOperator::idFromUsername($vals['contact']) === NULL)
            $errs[] = 'Contact not found';

        // Type, category, severity valid attribute codes for this workspace
        if(!AttributeDatabaseHandler::selectIdByCode($workspace->getId(), 'type', (string)$vals['type']))
            $errs[] = 'Type is not valid';

        if(!AttributeDatabaseHandler::selectIdByCode($workspace->getId(), 'category', (string)$vals['category']))
            $errs[] = 'Category is not valid';

        if(!$ignoreSeverity AND !AttributeDatabaseHandler::selectIdByCode($workspace->getId(), 'severity', (string)$vals['severity']))
            $errs[] = 'Severity is not valid';

        if($vals['closureCode'] !== NULL AND strlen($vals['closureCode']) !== 0 AND !AttributeDatabaseHandler::selectIdByCode($workspace->getId(), 'closureCode', (string)$vals['closureCode']))
            $errs[] = 'Closure code is not valid';

        // Dates are not set or valid dates (desiredDate, scheduledDate)
        if($vals['desiredDate'] !== NULL AND strlen($vals['desiredDate']) !== 0 AND !Validator::validDate((string)$vals['desiredDate']))
            $errs[] = 'Desired date is not valid';

        if($vals['scheduledDate'] !== NULL AND strlen($vals['scheduledDate']) !== 0 AND !Validator::validDate((string)$vals['scheduledDate']))
            $errs[] = 'Scheduled date is not valid';

        // Status code is 'new' or 'clo' OR valid attribute
        if(!in_array($vals['status'], array_keys(Ticket::STATIC_STATUSES)) AND !AttributeDatabaseHandler::selectIdByCode($workspace->getId(), 'status', (string)$vals['status']))
            $errs[] = 'Status is not valid';

        // If status code is 'clo', closure code must be set
        if($vals['status'] == 'clo' AND ($vals['closureCode'] === NULL OR strlen($vals['closureCode']) === 0))
            $errs[] = 'Closure code is required';

        if(!empty($errs))
            throw new ValidationError($errs);

        return TRUE;
    }

    /**
     * @param Workspace $workspace
     * @param $vals
     * @return array
     * @throws \exceptions\DatabaseException
     */
    private static function formatValues(Workspace $workspace, $vals): array
    {
        // Contact
        if($vals['contact'] !== NULL AND strlen($vals['contact']) === 0)
            $vals['contact'] = NULL;

        // Attributes
        $vals['type'] = AttributeDatabaseHandler::selectIdByCode($workspace->getId(), 'type', $vals['type']);
        $vals['category'] = AttributeDatabaseHandler::selectIdByCode($workspace->getId(), 'category', $vals['category']);
        $vals['severity'] = ($vals['severity'] !== NULL AND strlen($vals['severity'] !== 0)) ? AttributeDatabaseHandler::selectIdByCode($workspace->getId(), 'severity', $vals['severity']) : NULL;
        $vals['closureCode'] = ($vals['closureCode'] !== NULL AND strlen($vals['closureCode']) !== 0) ? AttributeDatabaseHandler::selectIdByCode($workspace->getId(), 'closureCode', $vals['closureCode']) : NULL;

        // Dates
        if(strlen($vals['desiredDate']) === 0) // Date errors handled by validator
            $vals['desiredDate'] = NULL;
        if(strlen($vals['scheduledDate']) === 0)
            $vals['scheduledDate'] = NULL;

        // Closure code
        if(strlen($vals['closureCode']) === 0)
            $vals['closureCode'] = NULL;

        return $vals;
    }
}