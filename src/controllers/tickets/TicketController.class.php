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


namespace controllers\tickets;


use business\tickets\AttributeOperator;
use business\tickets\TicketOperator;
use business\tickets\WorkspaceOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use exceptions\SecurityException;
use models\HTTPRequest;
use models\HTTPResponse;
use models\tickets\Ticket;

/**
 * Class TicketController
 *
 * Controller for accessing tickets
 *
 * @package controllers\tickets
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
        $this->workspace = WorkspaceOperator::getWorkspace((int)$workspace);

        // User must be in a team assigned to workspace
        if(!WorkspaceOperator::currentUserInWorkspace($this->workspace))
            throw new SecurityException('You are not a member of this workspace', SecurityException::USER_NO_PERMISSION);

        parent::__construct($request);
    }

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('tickets-agent');

        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            if($param == 'myAssignments')
                return $this->getMyAssignments();
            if($param == 'open')
                return $this->getOpen();
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            if($param === 'search')
                return $this->search();
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
    private function search(): HTTPResponse
    {
        $body = self::getFormattedBody(TicketOperator::SEARCH_FIELDS, FALSE);

        return $this->returnTickets(TicketOperator::getSearchResults($this->workspace, $body));
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
            $status = NULL;

            if(in_array($ticket->getStatus(), array_keys(Ticket::STATIC_STATUSES)))
                $status = Ticket::STATIC_STATUSES[$ticket->getStatus()];
            else
            {
                try{$status = AttributeOperator::getByCode(WorkspaceOperator::getWorkspace($ticket->getWorkspace()), 'status', $ticket->getStatus())->getName();}
                catch(EntryNotFoundException $e){}
            }

            $data[] = array(
                'number' => $ticket->getNumber(),
                'type' => AttributeOperator::nameFromId($ticket->getType()),
                'category' => AttributeOperator::nameFromId($ticket->getCategory()),
                'severity' => AttributeOperator::nameFromId((int)$ticket->getSeverity()),
                'status' => $status
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }
}