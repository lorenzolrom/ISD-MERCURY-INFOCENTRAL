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


namespace extensions\tickets\business;


use business\HistoryOperator;
use business\NotificationOperator;
use business\Operator;
use business\UserOperator;
use controllers\CurrentUserController;
use extensions\tickets\database\AttributeDatabaseHandler;
use extensions\tickets\database\TeamDatabaseHandler;
use extensions\tickets\database\TicketDatabaseHandler;
use extensions\tickets\database\UpdateDatabaseHandler;
use extensions\tickets\database\WorkspaceDatabaseHandler;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use extensions\tickets\models\Ticket;
use extensions\tickets\models\Workspace;
use models\User;
use utilities\HistoryRecorder;
use utilities\Validator;

class TicketOperator extends Operator
{
    public const FIELDS = array('title', 'contact', 'type', 'category', 'status', 'closureCode', 'severity', 'desiredDate', 'scheduledDate', 'description', 'assignees', 'notifyAssignees', 'notifyContact');
    public const SEARCH_FIELDS = array('title', 'number', 'contact', 'type', 'category', 'status', 'closureCode', 'severity', 'desiredDateStart', 'desiredDateEnd', 'scheduledDateStart', 'scheduledDateEnd', 'assignees', 'description');

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
        foreach(array('desiredDateStart', 'desiredDateEnd', 'scheduledDateStart', 'scheduledDateEnd') as $val)
        {
            if($vals[$val] === NULL)
                continue;

            $vals[$val] = trim($vals[$val], '%'); // Remove wildcards

            if(!Validator::validDate($vals[$val])) // Convert invalid dates
            {
                if(strpos($val, 'Start'))
                    $vals[$val] = '1000-01-01';
                else
                    $vals[$val] = '9999-12-31';
            }
        }

        // If there is no search for description, don't pass it to search
        if(strlen(trim($vals['description'], '%')) === 0)
            $vals['description'] = NULL;

        $assignees = array();

        if(is_array($vals['assignees']) AND !empty($vals['assignees']))
        {
            // Break up codes

            foreach($vals['assignees'] as $code)
            {
                $assignees[] = explode('-', $code);
            }
        }

        return TicketDatabaseHandler::select($workspace->getId(), $vals['number'], $vals['title'], $vals['contact'], $vals['type'], $vals['category'], $vals['status'], $vals['closureCode'], $vals['severity'],
            $vals['desiredDateStart'], $vals['desiredDateEnd'], $vals['scheduledDateStart'], $vals['scheduledDateEnd'], $vals['description'], $assignees);
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
        HistoryRecorder::writeAssocHistory($history, array('systemEntry' => array('Appended Description')));

        if(is_array($vals['assignees']))
        {
            TicketOperator::addAssignees($ticket, $vals['assignees'], TRUE, $history);
        }

        if($vals['notifyAssignees'] == 'true')
        {
            self::notifyAssignees($ticket);
            HistoryRecorder::writeAssocHistory($history, array('systemEntry' => array('Assignee Notification Sent')));
        }

        if($vals['notifyContact'] == 'true')
        {
            self::notifyContact($ticket);
            HistoryRecorder::writeAssocHistory($history, array('systemEntry' => array('Contact Notification Sent')));
        }

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

        if(is_array($vals['assignees']))
        {
            TicketOperator::addAssignees($ticket, $vals['assignees'], TRUE, $history);
        }

        if($vals['notifyAssignees'] == 'true')
        {
            self::notifyAssignees($ticket);
            HistoryRecorder::writeAssocHistory($history, array('systemEntry' => array('Assignee Notification Sent')));
        }

        if($vals['notifyContact'] == 'true')
        {
            self::notifyContact($ticket);
            HistoryRecorder::writeAssocHistory($history, array('systemEntry' => array('Contact Notification Sent')));
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
        $user = CurrentUserController::currentUser();

        // Set default values
        $vals['status'] = Ticket::NEW; // New
        $vals['closureCode'] = NULL; // No closure code
        $vals['severity'] = NULL; // No assigned severity
        $vals['contact'] = $user->getUsername(); // Submitting user is contact
        $vals['scheduledDate'] = NULL; // No scheduled date
        $vals['assignees'] = NULL; // No assignees
        $vals['notifyAssignees'] = 'true'; // Send email to assignees
        $vals['notifyContact'] = 'false';

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
        else if($ticket->getStatus() == Ticket::REOPENED)
            $vals['status'] = $ticket::REOPENED;
        else
            $vals['status'] = Ticket::RESPONDED; // Customer responded

        $vals['severity'] = $ticket->getSeverity();

        $history = HistoryRecorder::writeHistory('Tickets_Ticket', HistoryRecorder::MODIFY, $ticket->getId(), $ticket, $vals, array('severity', 'desiredDate', 'scheduledDate'));
        $ticket = TicketDatabaseHandler::update($ticket->getId(), $ticket->getTitle(), $ticket->getContact(), $ticket->getType(), $ticket->getCategory(), Ticket::RESPONDED, $ticket->getClosureCode(), $ticket->getSeverity(), $ticket->getDesiredDate(), $ticket->getScheduledDate());

        UpdateDatabaseHandler::insert($ticket->getId(), CurrentUserController::currentUser()->getId(), $description);
        HistoryRecorder::writeAssocHistory($history, array('systemEntry' => array('Appended Description')));
        HistoryRecorder::writeAssocHistory($history, array('systemEntry' => array('Assignee Notification Sent')));

        self::notifyAssignees($ticket); // Notify assignees

        return $ticket;
    }

    /**
     * @param User $user
     * @return Ticket[]
     * @throws \exceptions\DatabaseException
     */
    public static function getOpenRequests(User $user): array
    {
        return TicketDatabaseHandler::selectByContactAndStatus($user->getUsername());
    }

    /**
     * @param User $user
     * @return Ticket[]
     * @throws \exceptions\DatabaseException
     */
    public static function getClosedRequests(User $user): array
    {
        return TicketDatabaseHandler::selectByContactAndStatus($user->getUsername(), TRUE);
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
                $name = $column['column'];

                if($name == 'systemEntry') // This gets passed right through
                {
                    $columnStrings[] = $column['newValue'];
                }
                else if($name == 'assign')
                {
                    $value = $column['newValue'];
                    $operation = substr($value, 0, 1);
                    $value = substr($value, 1); // Overwrite old value removing the operation character

                    // Determine if assignee added or removed
                    if($operation == '+')
                        $message = 'Assigned ';
                    else
                        $message = 'Removed  assigned ';

                    // Determine if team or user
                    $parts = explode('-', $value);

                    if(!isset($parts[1]))
                    {
                        $message .= 'team: ';
                        $team = TeamOperator::getTeam((int)$parts[0]);

                        $message .= $team->getName();
                    }
                    else
                    {
                        $message .= 'user: ';
                        $team = TeamOperator::getTeam((int)$parts[0]);
                        $user = UserOperator::getUser((int)$parts[1]);

                        $message .= $team->getName() . ' - ' . $user->getFirstName() . ' ' . $user->getLastName() . ' (' . $user->getUsername() . ')';
                    }

                    $columnStrings[] = $message;

                }
                else if($name == 'link')
                {
                    $value = $column['newValue'];
                    $operation = substr($value, 0, 1);
                    $value = substr($value, 1); // Overwrite old value removing the operation character

                    if($operation == '+')
                        $message = 'Linked to ticket #' . $value;
                    else
                        $message = 'Unlinked from ticket #' . $value;

                    $columnStrings[] = $message;
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
     * @param Ticket $ticket
     * @param array $assignees This should be array of strings containing team ID or "team ID"-"user ID"
     * @param bool $deleteExisting
     * @param int|null $hist
     * @return bool
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function addAssignees(Ticket $ticket, array $assignees, bool $deleteExisting = FALSE, ?int $hist = NULL): bool
    {
        if(empty($assignees) AND !$deleteExisting)
            return FALSE; // Nothing to do...

        if($hist === NULL)
            $hist = HistoryRecorder::writeHistory('Tickets_Ticket', HistoryRecorder::MODIFY, $ticket->getId(), $ticket);

        $currentAssignees = TicketDatabaseHandler::selectAssignees($ticket->getId());

        // Convert indexed assignees array to array of strings in '-' notation
        $formattedAssignees = array();

        foreach($currentAssignees as $assignee)
        {
            if(strlen($assignee['user']) === 0 OR $assignee['user'] == NULL)
                $formattedAssignees[] = $assignee['team'];
            else
                $formattedAssignees[] = $assignee['team'] . '-' . $assignee['user'];
        }

        $currentAssignees = $formattedAssignees;
        unset($formattedAssignees);

        $addedAssignees = array(); // This will be compared with the above to populate the following two arrays:

        $removedTeams = array();
        $removedUsers = array();

        // Remove team only assignment if the given assignment contains a team member
        foreach($assignees as $assignee)
        {
            $parts = explode('-', $assignee);

            if(sizeof($parts) != 2) // If this is not a user assignment
                continue;

            $teamId = $parts[0];

            $key = array_search($teamId, $assignees);

            if($key !== FALSE)
                unset($assignees[$key]);
        }

        foreach($assignees as $assignee)
        {
            $assigneeParts = explode('-', $assignee);

            if(sizeof($assigneeParts) === 1) // only team
            {
                $teamId = $assigneeParts[0];

                if((TicketDatabaseHandler::isTeamAssignedAtAll($ticket->getId(), $teamId) AND !in_array($teamId, $assignees)) OR TicketDatabaseHandler::isTeamOnlyAssigned($ticket->getId(), $teamId)) // Skip if team already assigned and not being added
                    continue;

                $team = TeamOperator::getTeam($teamId);

                if(!WorkspaceDatabaseHandler::teamInWorkspace($ticket->getWorkspace(), $team->getId()))
                {
                    throw new ValidationError(array('Team is not in this workspace'));
                }

                HistoryRecorder::writeAssocHistory($hist, array('assign' => array('+' . $team->getId())));
                TicketDatabaseHandler::addAssignee($ticket->getId(), $team->getId(), NULL);
                $addedAssignees[] = (string)$team->getId();
            }
            else if(sizeof($assigneeParts) === 2) // Team and user
            {
                $teamId = $assigneeParts[0];
                $userId = $assigneeParts[1];

                if(TicketDatabaseHandler::isAssigned($ticket->getId(), $teamId, $userId)) // Skip if user already assigned
                    continue;

                $team = TeamOperator::getTeam($teamId);

                if(!WorkspaceDatabaseHandler::teamInWorkspace($ticket->getWorkspace(), $team->getId()))
                {
                    throw new ValidationError(array('Team is not in this workspace'));
                }

                $user = UserOperator::getUser($userId);

                if(!TeamDatabaseHandler::userInTeam($team->getId(), $user->getId()))
                {
                    throw new ValidationError(array('User is not a member of this team'));
                }

                HistoryRecorder::writeAssocHistory($hist, array('assign' => array('+' . $team->getId() . '-' . $user->getId())));
                TicketDatabaseHandler::addAssignee($ticket->getId(), $team->getId(), $user->getId());
                $addedAssignees[] = $team->getId() . '-' . $user->getId();

                // Remove team entry alone if a user is present
                if(TicketDatabaseHandler::isTeamOnlyAssigned($ticket->getId(), $team->getId()))
                {
                    TicketDatabaseHandler::removeAssignedTeamOnly($ticket->getId(), $team->getId());
                }
            }
        }

        //Sort removed assignees into teams and users
        foreach($currentAssignees as $assignee)
        {
            if(!in_array($assignee, $addedAssignees) AND !in_array($assignee, $assignees))
            {
                $assigneeParts = explode('-', $assignee);

                if(sizeof($assigneeParts) === 1)
                    $removedTeams[] = $assigneeParts[0];
                else
                    $removedUsers[] = $assigneeParts[0] . '-' . $assigneeParts[1];
            }
        }

        if(!$deleteExisting) // If we are not removing existing assignees, do not execute the following
            return TRUE;

        // Create removal history records and remove users from ticket
        foreach($removedTeams as $team)
        {
            HistoryRecorder::writeAssocHistory($hist, array('assign' => array('_' . $team)));
            TicketDatabaseHandler::removeAssignedTeamOnly($ticket->getId(), $team);
        }

        foreach($removedUsers as $user)
        {
            HistoryRecorder::writeAssocHistory($hist, array('assign' => array('_' . $user)));
            $assigneeParts = explode('-', $user);
            TicketDatabaseHandler::removeAssignee($ticket->getId(), $assigneeParts[0], $assigneeParts[1]);
        }

        return TRUE;
    }

    /**
     * @param Ticket $ticket
     * @param string $assigneeCode
     * @return bool
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function removeAssignee(Ticket $ticket, string $assigneeCode): bool
    {
        // get team and user ID
        $assigneeCodeParts = explode('-', $assigneeCode);

        if(sizeof($assigneeCodeParts) === 1) // if only team is specified, remove entire team from ticket
        {
            $team = TeamOperator::getTeam((int)$assigneeCodeParts[0]);

            $hist = HistoryRecorder::writeHistory('Tickets_Ticket', HistoryRecorder::MODIFY, $ticket->getId(), $ticket);

            HistoryRecorder::writeAssocHistory($hist, array('assign' => array('_' . $team->getId())));

            $assignedMembers = TicketDatabaseHandler::selectAssignedTeamUsers($ticket->getId(), $team->getId());

            foreach($assignedMembers as $assignedMember)
            {
                HistoryRecorder::writeAssocHistory($hist, array('assign' => array('_' . $team->getId() . '-' . $assignedMember)));
            }

            return TicketDatabaseHandler::removeAssignedTeam($ticket->getId(), $team->getId());
        }
        else if(sizeof($assigneeCodeParts) === 2)
        {
            $team = TeamOperator::getTeam((int)$assigneeCodeParts[0]);
            $user = UserOperator::getUser((int)$assigneeCodeParts[1]);

            $hist = HistoryRecorder::writeHistory('Tickets_Ticket', HistoryRecorder::MODIFY, $ticket->getId(), $ticket);
            HistoryRecorder::writeAssocHistory($hist, array('assign' => array('_' . $team->getId() . '-' . $user->getId())));

            return TicketDatabaseHandler::removeAssignee($ticket->getId(), $team->getId(), $user->getId());
        }

        return FALSE;
    }

    /**
     * Returns a human readable message of how long it has been since the supplied DATETIME stamp
     * @param string $time
     * @return string
     */
    public static function getTimeSince(string $time): string
    {
        try
        {
            $start_date = new \DateTime($time);
            $since_start = $start_date->diff(new \DateTime(date("Y-m-d H:i:s")));

            $years = $since_start->y.' yr ';
            $months = $since_start->m.' mos ';
            $days = $since_start->d.' days ';
            $hours = $since_start->h.' hrs ';
            $minutes = $since_start->i.' min ';

            $formattedDate = "";

            if($years != 0)
                $formattedDate .= $years;
            if($months != 0)
                $formattedDate .= $months;
            if($years == 0 AND $days != 0)
                $formattedDate .= $days;
            if($years == 0 AND $months == 0 AND $hours != 0)
                $formattedDate .= $hours;
            if($years == 0 AND $months == 0 AND $days == 0 AND $minutes != 0)
                $formattedDate .= $minutes;

            if(empty($formattedDate))
                $formattedDate .= "now";
            else
                $formattedDate .= "ago";

            return $formattedDate;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }

    /**
     * @param int $workspaceId
     * @param string $query
     * @return Ticket[]
     * @throws \exceptions\DatabaseException
     */
    public static function runQuickSearch(int $workspaceId, string $query): array
    {
        return TicketDatabaseHandler::quickSearch($workspaceId, $query);
    }

    /**
     * @param Ticket $ticket1
     * @param int $ticket2Number
     * @return bool
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function link(Ticket $ticket1, int $ticket2Number): bool
    {
        if($ticket1->getNumber() == $ticket2Number)
        {
            throw new ValidationError(array('Cannot link ticket to itself'));
        }

        $ticket2 = TicketDatabaseHandler::selectByNumber($ticket1->getWorkspace(), $ticket2Number);

        // Check if link exists already
        if(TicketDatabaseHandler::areTicketsLinked($ticket1->getId(), $ticket2->getId()))
        {
            throw new ValidationError(array('Tickets are already linked'));
        }

        // Ticket 1 history
        $hist = HistoryRecorder::writeHistory('Tickets_Ticket', HistoryRecorder::MODIFY, $ticket1->getId(), $ticket1);
        HistoryRecorder::writeAssocHistory($hist, array('link' => array('+' . $ticket2->getNumber())));

        // Ticket 2 history
        $hist = HistoryRecorder::writeHistory('Tickets_Ticket', HistoryRecorder::MODIFY, $ticket2->getId(), $ticket2);
        HistoryRecorder::writeAssocHistory($hist, array('link' => array('+' . $ticket1->getNumber())));

        return TicketDatabaseHandler::insertLink($ticket1->getId(), $ticket2->getId());
    }

    /**
     * @param Ticket $ticket1
     * @param int $ticket2Number Ticket NUMBER
     * @return bool
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function unlink(Ticket $ticket1, int $ticket2Number): bool
    {
        $ticket2 = TicketDatabaseHandler::selectByNumber($ticket1->getWorkspace(), $ticket2Number);

        if(!TicketDatabaseHandler::areTicketsLinked($ticket1->getId(), $ticket2->getId()))
        {
            throw new ValidationError(array('Tickets are not linked'));
        }

        // Ticket 1 history
        $hist = HistoryRecorder::writeHistory('Tickets_Ticket', HistoryRecorder::MODIFY, $ticket1->getId(), $ticket1);
        HistoryRecorder::writeAssocHistory($hist, array('link' => array('-' . $ticket2->getNumber())));

        // Ticket 2 history
        $hist = HistoryRecorder::writeHistory('Tickets_Ticket', HistoryRecorder::MODIFY, $ticket2->getId(), $ticket2);
        HistoryRecorder::writeAssocHistory($hist, array('link' => array('-' . $ticket1->getNumber())));

        return TicketDatabaseHandler::deleteLink($ticket1->getId(), $ticket2->getId());
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
        if($vals['status'] == Ticket::CLOSED AND ($vals['closureCode'] === NULL OR strlen($vals['closureCode']) === 0))
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

    /**
     * @param Ticket $ticket
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private static function notifyAssignees(Ticket $ticket)
    {
        $workspace = WorkspaceOperator::getWorkspace($ticket->getWorkspace());
        $requestor = '';

        if(strlen($ticket->getContact()) !== 0)
        {
            $contact = UserOperator::getUserByUsername($ticket->getContact());
            $requestor = $contact->getFirstName() . ' ' . $contact->getLastName() . ' (' . $contact->getUsername() . ')';
        }

        $title = $ticket->getTitle() . ' [TICKET=' . $ticket->getNumber() . ' WORKSPACE=' . $ticket->getWorkspace() . ']';
        $update = $ticket->getLastUpdate();

        $message = "<p style='color: #888888;'><em>E-Mail Notification from " . $workspace->getName() . "</em></p>

        <p>You are receiving this e-mail because a ticket in your queue has been updated</p>
        
        <p><strong>Title: </strong>{$ticket->getTitle()}</p>
        <p><strong>Ticket Number: </strong><a href='" . \Config::OPTIONS['serviceCenterAgentURL'] . $ticket->getNumber() . "?w=" . $ticket->getWorkspace() . "'>{$ticket->getNumber()}</a></p>
        <p><strong>Requestor: </strong> $requestor</p>
        <p><strong>Status: </strong> " . TicketOperator::getTicketStatusName($ticket) . "</p>
        
        <p><strong>Last Update:</strong> Entered {$update->getTime()} by " . UserOperator::usernameFromId($update->getUser()) . "</p>
        <div style='background-color: #e3e3e3;'>
            {$update->getDescription()}
        </div>";

        // if user is assigned, only add them once (if they are assigned as multiple teams)
        // if team is assigned, add all members of team ensuring they are not duplicated

        $userIds = array();
        $assignees = $ticket->getAssignees();

        if(empty($assignees)) // If nobody is assigned
        {
            foreach($workspace->getTeams() as $team) // Assign everyone in workspace
            {
                $assignee = array();
                $assignee['team'] = $team->getId();
                $assignee['user'] = '';

                $assignees[] = $assignee;
            }
        }

        foreach($assignees as $assignee)
        {
            $teamId = (int)$assignee['team'];
            $userId = (int)$assignee['user'];

            // user is not empty (probably doesn't work), user type exactly equal to null (also, probably not), casted user ID is zero (what trips this condition)
            if(strlen($userId) === 0 OR $userId === NULL OR (int)$userId === 0) // Only team is assigned
            {
                $team = TeamOperator::getTeam((int)$teamId);
                foreach($team->getUsers() as $user)
                {
                    if(!in_array($user->getId(), $userIds))
                        $userIds[] = $user->getId();
                }
            }
            else
            {
                if(!in_array($userId, $userIds))
                    $userIds[] = $userId;
            }
        }

        $users = array();

        foreach($userIds as $userId)
        {
            $users[] = UserOperator::getUser((int)$userId);
        }

        NotificationOperator::bulkSendToUsers($title, $message, 0, TRUE, $users);

    }

    /**
     * @param Ticket $ticket
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private static function notifyContact(Ticket $ticket)
    {
        if(strlen($ticket->getContact()) === 0) // no contact
            return;

        $user = UserOperator::getUserByUsername($ticket->getContact());

        if(strlen($user->getEmail()) === 0) // no email
            return;

        $workspace = WorkspaceOperator::getWorkspace($ticket->getWorkspace());

        $title = $ticket->getTitle() . ' [TICKET=' . $ticket->getNumber() . ' WORKSPACE=' . $ticket->getWorkspace() . ']';
        $action = 'updated';

        if($ticket->getStatus() == Ticket::NEW)
            $action = 'opened';
        else if($ticket->getStatus() == Ticket::CLOSED)
            $action = 'closed';

        $message = "<p style='color: #888888;'><em>E-Mail Notification from " . $workspace->getName() . "</em></p>

        <p>Your support ticket <a href='" . \Config::OPTIONS['serviceCenterRequestURL'] . "{$ticket->getNumber()}'>{$ticket->getNumber()}</a> (<span style='color: red; font-weight: bold;'>{$ticket->getTitle()}</span>) has been <strong>$action</strong> with the following details:</p>
        
        <div style='background-color: #e3e3e3;'>
            {$ticket->getLastUpdate()->getDescription()}
        </div>";

        NotificationOperator::bulkSendToUsers($title, $message, 0, TRUE, array($user));
    }

    /**
     * @param Ticket $ticket
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function getTicketStatusName(Ticket $ticket)
    {
        $status = NULL;

        if(in_array($ticket->getStatus(), array_keys(Ticket::STATIC_STATUSES)))
            $status = Ticket::STATIC_STATUSES[$ticket->getStatus()];
        else
        {
            try{$status = AttributeOperator::getByCode(WorkspaceOperator::getWorkspace($ticket->getWorkspace()), 'status', $ticket->getStatus())->getName();}
            catch(EntryNotFoundException $e){}
        }

        return $status;
    }

    /**
     * @param User $user
     * @param int $workspace
     * @param int $number
     * @return Ticket
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function getRequest(User $user, int $workspace, int $number): Ticket
    {
        return TicketDatabaseHandler::selectByWorkspaceNumberContact($workspace, $number, $user->getUsername());
    }
}