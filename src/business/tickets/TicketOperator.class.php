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


use business\Operator;
use business\UserOperator;
use controllers\CurrentUserController;
use database\tickets\AttributeDatabaseHandler;
use database\tickets\TicketDatabaseHandler;
use database\tickets\UpdateDatabaseHandler;
use exceptions\ValidationError;
use models\tickets\Ticket;
use models\tickets\Workspace;
use utilities\HistoryRecorder;
use utilities\Validator;

class TicketOperator extends Operator
{
    public const FIELDS = array('title', 'contact', 'type', 'category', 'status', 'closureCode', 'severity', 'desiredDate', 'scheduledDate', 'description');
    public const SEARCH_FIELDS = array('title', 'number', 'contact', 'type', 'category', 'status', 'closureCode', 'severity', 'desiredStart', 'desiredEnd', 'scheduledStart', 'scheduledEnd');

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
     * @return Ticket
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function createTicket(Workspace $workspace, array $vals): Ticket
    {
        self::validateTicket($workspace, $vals);

        // Update required for new ticket
        if($vals['description'] === NULL OR strlen($vals['description']))
            throw new ValidationError(array('Initial description is required'));

        // Convert
        $vals = self::formatValues($workspace, $vals);

        $ticket = TicketDatabaseHandler::insert($workspace->getId(), TicketDatabaseHandler::nextNumber($workspace->getId()), $vals['title'], $vals['contact'],
            $vals['type'], $vals['category'], $vals['status'], $vals['closureCode'], $vals['severity'], $vals['desiredDate'], $vals['scheduledDate']);

        $history = HistoryRecorder::writeHistory('Tickets_Ticket', HistoryRecorder::CREATE, $ticket->getId(), $ticket);

        // Create update
        UpdateDatabaseHandler::insert($ticket->getId(), CurrentUserController::currentUser()->getId(), $vals['description']);
        HistoryRecorder::writeAssocHistory($history, array('appendDescription' => array($vals['description'])));
        
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
            HistoryRecorder::writeAssocHistory($history, array('appendDescription' => array($vals['description'])));
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

        return self::createTicket($workspace, $vals);
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
        HistoryRecorder::writeAssocHistory($history, array('appendDescription' => array($description)));

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
     * @param array $vals
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws ValidationError
     */
    private static function validateTicket(Workspace $workspace, array $vals): bool
    {
        $errs = array();

        if($vals['title'] === NULL OR strlen($vals['title']) === 0)
            $errs[] = 'Title is required';

        // Contact valid or empty
        if(strlen($vals['contact']) !== 0 AND UserOperator::idFromUsername($vals['contact']) === NULL)
            $errs[] = 'Contact not found';

        // Type, category, severity valid attribute codes for this workspace
        if(!AttributeDatabaseHandler::selectIdByCode($workspace->getId(), 'type', $vals['type']))
            $errs[] = 'Type is not valid';

        if(!AttributeDatabaseHandler::selectIdByCode($workspace->getId(), 'category', $vals['category']))
            $errs[] = 'Category is not valid';

        if($vals['severity'] !== NULL AND strlen($vals['severity']) !== 0 AND !AttributeDatabaseHandler::selectIdByCode($workspace->getId(), 'severity', $vals['severity']))
            $errs[] = 'Severity is not valid';

        if($vals['closureCode'] !== NULL AND strlen($vals['closureCode']) !== 0 AND !AttributeDatabaseHandler::selectIdByCode($workspace->getId(), 'closureCode', $vals['closureCode']))
            $errs[] = 'Closure code is not valid';

        // Dates are not set or valid dates (desiredDate, scheduledDate)
        if($vals['desiredDate'] !== NULL AND strlen($vals['desiredDate']) !== 0 AND !Validator::validDate($vals['desiredDate']))
            $errs[] = 'Desired date is not valid';

        if($vals['scheduledDate'] !== NULL AND strlen($vals['scheduledDate']) !== 0 AND !Validator::validDate($vals['scheduledDate']))
            $errs[] = 'Scheduled date is not valid';

        // Status code is 'new' or 'clo' OR valid attribute
        if(!in_array($vals['status'], array_keys(Ticket::STATIC_STATUSES)) AND !AttributeDatabaseHandler::selectIdByCode($workspace->getId(), 'status', $vals['status']))
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