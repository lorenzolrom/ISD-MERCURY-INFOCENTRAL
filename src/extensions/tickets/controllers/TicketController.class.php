<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 6/05/2019
 * Time: 9:17 AM
 */


namespace extensions\tickets\controllers;


use exceptions\EntryIsBusyException;
use extensions\tickets\business\AttributeOperator;
use extensions\tickets\business\LockOperator;
use extensions\tickets\business\TeamOperator;
use extensions\tickets\business\TicketOperator;
use extensions\tickets\business\WorkspaceOperator;
use business\UserOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use exceptions\SecurityException;
use models\HTTPRequest;
use models\HTTPResponse;
use extensions\tickets\models\Ticket;

/**
 * Class TicketController
 *
 * Controller for accessing tickets
 *
 * @package extensions\tickets\controllers
 */
class TicketController extends Controller
{
    private $workspace;

    /**
     * TicketController constructor.
     * @param string $workspace
     * @param HTTPRequest $request
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function __construct(string $workspace, HTTPRequest $request)
    {
        CurrentUserController::validatePermission('tickets-agent');

        $this->workspace = WorkspaceOperator::getWorkspace((int)$workspace);

        // User must be in a team assigned to workspace
        if(!WorkspaceOperator::currentUserInWorkspace($this->workspace))
            throw new SecurityException('You are not a member of this workspace', SecurityException::USER_NO_PERMISSION);

        parent::__construct($request);
    }

    /**
     * @return HTTPResponse|null
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationError
     * @throws \exceptions\EntryIsBusyException
     */
    public function getResponse(): ?HTTPResponse
    {
        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            if($param == 'myAssignments')
                return $this->getMyAssignments();
            else if($param == 'open')
                return $this->getOpen();
            else if($param == 'closed')
                return $this->getClosed();
            else
            {
                $action = $this->request->next();
                if($action == 'updates')
                    return $this->getUpdates((int)$param);
                else if($action == 'history')
                    return $this->getHistory((int)$param);
                else if($action == 'assignees')
                    return $this->getAssignees((int)$param);
                else if($action == 'linked')
                    return $this->getLinked((int)$param);

                // Check if the edit flag was passed
                return $this->getTicket((int)$param, $action == 'edit' ? TRUE : FALSE);
            }
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            $action = $this->request->next();

            if($param === 'search')
                return $this->search();
            else if($param === 'quickSearch')
                return $this->quickSearch();
            else if($action == 'link')
                return $this->link($param);
            else
                return $this->createTicket();
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            $action = $this->request->next();

            if($action == 'assignees')
                return $this->assign($param);
            if($action == 'lock')
                return $this->lock((int)$param);

            return $this->updateTicket((int)$param);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            $action = $this->request->next();

            if($action == 'assignee')
                return $this->removeAssignee($param);
            if($action == 'link')
                return $this->unlink($param, $this->request->next());
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     */
    private function getMyAssignments(): HTTPResponse
    {
        return $this->returnTickets(TicketOperator::getMyAssignments($this->workspace));
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getOpen(): HTTPResponse
    {
        return $this->returnTickets(TicketOperator::getOpenTickets($this->workspace));
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getClosed(): HTTPResponse
    {
        return $this->returnTickets(TicketOperator::getClosedTickets($this->workspace));
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function search(): HTTPResponse
    {
        $body = self::getFormattedBody(TicketOperator::SEARCH_FIELDS, FALSE);

        return $this->returnTickets(TicketOperator::getSearchResults($this->workspace, $body));
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function quickSearch(): HTTPResponse
    {
        $query = self::getFormattedBody(array('query'), TRUE);

        return $this->returnTickets(TicketOperator::runQuickSearch($this->workspace->getId(), $query['query']));
    }

    /**
     * @param Ticket[] $tickets
     * @return HTTPResponse
     *
     * Common function for returning list of tickets
     * @throws \exceptions\DatabaseException
     */
    private function returnTickets(array $tickets): HTTPResponse
    {
        $data = array();

        foreach($tickets as $ticket)
        {
            $status = TicketOperator::getTicketStatusName($ticket);

            $data[] = array(
                'number' => $ticket->getNumber(),
                'title' => $ticket->getTitle(),
                'type' => AttributeOperator::nameFromId($ticket->getType()),
                'category' => AttributeOperator::nameFromId($ticket->getCategory()),
                'severity' => AttributeOperator::nameFromId((int)$ticket->getSeverity()),
                'status' => $status,
                'scheduledDate' => $ticket->getScheduledDate(),
                'lastUpdate' => TicketOperator::getTimeSince($ticket->getLastUpdateTime())
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse containing the new ticket number
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationError
     */
    private function createTicket(): HTTPResponse
    {
        return new HTTPResponse(HTTPResponse::CREATED, array('number' => TicketOperator::createTicket($this->workspace, self::getFormattedBody(TicketOperator::FIELDS))->getNumber()));
    }

    /**
     * @param int $number
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationError
     * @throws \exceptions\EntryIsBusyException
     */
    private function updateTicket(int $number): HTTPResponse
    {
        $ticket = TicketOperator::getTicket($this->workspace, $number);

        TicketOperator::updateTicket($ticket, self::getFormattedBody(TicketOperator::FIELDS, TRUE));

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param int $number
     * @param bool $lockTicket // should the get create a lock?
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryIsBusyException
     */
    private function getTicket(int $number, bool $lockTicket = FALSE): HTTPResponse
    {
        $ticket = TicketOperator::getTicket($this->workspace, $number);

        // If a lock was requested, create an initial lock
        if($lockTicket)
        {
            try
            {
                LockOperator::updateLock($ticket); // Create a lock for this user
            }
            catch(EntryIsBusyException $e){} // Ignore, unable to lock, will be addressed later
        }

        $statusName = TicketOperator::getTicketStatusName($ticket);

        $data = array(
            'workspace' => WorkspaceOperator::getWorkspace($ticket->getWorkspace())->getName(),
            'number' => $ticket->getNumber(),
            'title' => $ticket->getTitle(),
            'contact' => $ticket->getContact(),
            'type' => AttributeOperator::codeFromId($ticket->getType()),
            'typeName' => AttributeOperator::nameFromId($ticket->getType()),
            'category' => AttributeOperator::codeFromId($ticket->getCategory()),
            'categoryName' => AttributeOperator::nameFromId($ticket->getCategory()),
            'status' => $ticket->getStatus(),
            'statusName' => $statusName,
            'closureCode' => AttributeOperator::codeFromId($ticket->getClosureCode()),
            'closureCodeName' => ($ticket->getClosureCode() == NULL) ? NULL : AttributeOperator::nameFromId((int)$ticket->getClosureCode()),
            'severity' => AttributeOperator::codeFromId($ticket->getSeverity()),
            'severityName' => ($ticket->getSeverity() == NULL) ? NULL : AttributeOperator::nameFromId((int)$ticket->getSeverity()),
            'desiredDate' => $ticket->getDesiredDate(),
            'scheduledDate' => $ticket->getScheduledDate(),
            'assignees' => $ticket->getAssigneeCodes(),
            'locked' => 'no',
            'lockedTime' => '',
            'lockedBy' => ''
        );

        // Get lock status
        $lock = LockOperator::getActiveLock($ticket);
        if($lock !== NULL AND $lock->getUser() !== CurrentUserController::currentUser()->getId()) // If ticket is locked and current user is not who locked it
        {
            $data['locked'] = 'yes';
            $data['lockedTime'] = $lock->getLastCheckin();
            $data['lockedBy'] = UserOperator::getUser($lock->getUser())->getUsername();
        }


        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param int $number
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getUpdates(int $number): HTTPResponse
    {
        $ticket = TicketOperator::getTicket($this->workspace, $number);

        $data = array();

        foreach($ticket->getUpdates() as $update)
        {
            $user = UserOperator::getUser($update->getUser());
            $data[] = array(
                'user' => $user->getUsername(),
                'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                'time' => $update->getTime(),
                'description' => $update->getDescription()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param int $number
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     */
    private function getHistory(int $number): HTTPResponse
    {
        $ticket = TicketOperator::getTicket($this->workspace, $number);

        return new HTTPResponse(HTTPResponse::OK, TicketOperator::getTicketHistory($ticket));
    }

    /**
     * @param int $number
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getAssignees(int $number): HTTPResponse
    {
        $ticket = TicketOperator::getTicket($this->workspace, $number);

        $assignees = TicketOperator::getTicketAssignees($ticket);

        $assigneeList = array(); // Team IDs as array keys, user IDs in those arrays

        foreach($assignees as $assignee)
        {
            $team = $assignee['team'];
            $user = $assignee['user'];

            // Add team if it has not been seen before
            if(!in_array($team, array_keys($assigneeList)))
            {
                $assigneeList[$team] = array();
            }

            // Add member to the team
            if(!in_array($user, $assigneeList[$team]))
                $assigneeList[$team][] = $user;
        }

        // Translate to data for display
        $finalList = array();

        foreach(array_keys($assigneeList) as $teamID)
        {
            $teamData = array();

            $team = TeamOperator::getTeam((int)$teamID);

            $teamData['id'] = $teamID;
            $teamData['name'] = $team->getName();

            $teamData['users'] = array();

            // Users
            foreach($assigneeList[$teamID] as $userID)
            {
                if($userID === NULL or strlen($userID) === 0)
                    continue;

                $user = UserOperator::getUser((int)$userID);

                $userData = array();
                $userData['id'] = $user->getId();
                $userData['username'] = $user->getUsername();
                $userData['name'] = $user->getFirstName() . " " . $user->getLastName();

                $teamData['users'][] = $userData;
            }

            $finalList[] = $teamData;
        }

        return new HTTPResponse(HTTPResponse::OK, $finalList);
    }

    /**
     * @param int $number
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationError
     */
    private function assign(int $number): HTTPResponse
    {
        $body = self::getFormattedBody(array('assignees', 'overwrite'), TRUE); // Array of teams and users in '-' format

        if($body['overwrite'] == 'true')
            $body['overwrite'] = TRUE;
        else
            $body['overwrite'] = FALSE;

        $ticket = TicketOperator::getTicket($this->workspace, $number);

        TicketOperator::addAssignees($ticket, (array)$body['assignees'], $body['overwrite']);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param int $number
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     */
    private function removeAssignee(int $number): HTTPResponse
    {
        $body = self::getFormattedBody(array('assignee'), TRUE);

        $ticket = TicketOperator::getTicket($this->workspace, $number);

        TicketOperator::removeAssignee($ticket, (string)$body['assignee']);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param int $number
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationError
     */
    private function link(int $number): HTTPResponse
    {
        $body = self::getFormattedBody(array('linkedNumber'), TRUE);

        TicketOperator::link(TicketOperator::getTicket($this->workspace, $number), (int)$body['linkedNumber']);

        return new HTTPResponse(HTTPResponse::CREATED);
    }

    /**
     * @param int $number
     * @param int $linkedNumber
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationError
     */
    private function unlink(int $number, int $linkedNumber): HTTPResponse
    {
        $ticket1 = TicketOperator::getTicket($this->workspace, $number);

        TicketOperator::unlink($ticket1, $linkedNumber);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param int $number
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getLinked(int $number): HTTPResponse
    {
        $ticket = TicketOperator::getTicket($this->workspace, $number);

        $data = array();

        foreach($ticket->getLinked() as $t)
        {
            $tData = array();

            $tData['number'] = $t->getNumber();
            $tData['title'] = $t->getTitle();

            $data[] = $tData;
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param int $number
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryIsBusyException
     */
    private function lock(int $number): HTTPResponse
    {
        $ticket = TicketOperator::getTicket($this->workspace, $number);
        LockOperator::updateLock($ticket);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}