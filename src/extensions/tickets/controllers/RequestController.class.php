<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 9/14/2019
 * Time: 11:22 AM
 */


namespace extensions\tickets\controllers;


use exceptions\DatabaseException;
use exceptions\SecurityException;
use exceptions\ValidationError;
use extensions\tickets\business\AttributeOperator;
use extensions\tickets\business\TicketOperator;
use extensions\tickets\business\WorkspaceOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class RequestController extends Controller
{
    private $user;

    /**
     * @return HTTPResponse|null
     * @throws EntryNotFoundException
     * @throws DatabaseException
     * @throws SecurityException
     * @throws ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('tickets-customer');
        $this->user = CurrentUserController::currentUser();
        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            if($param == 'attributes')
            {
                $next = $this->request->next();

                if($next === NULL)
                    return $this->getAllAttributes();
                else
                    return $this->getAttributesOfType((string)$next);
            }

            if($param == 'open')
                return $this->getOpenRequests();
            else if($param == 'closed')
                return $this->getClosedRequests();
            else // Search for specific workspace
            {
                $workspaceId = (int)$param;
                $ticketNum = (int)$this->request->next();

                if($this->request->next() == 'updates') // Get request updates
                    return $this->getUpdates($workspaceId, $ticketNum);

                return $this->getRequest($workspaceId, $ticketNum); // First param is workspace, second is request number
            }
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            return $this->newRequest();
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            $workspaceId = $param;
            $ticketNum = $this->request->next();

            return $this->updateRequest((int)$workspaceId, (int)$ticketNum);
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws DatabaseException
     */
    private function getOpenRequests(): HTTPResponse
    {
        return $this->returnRequests(TicketOperator::getOpenRequests($this->user));
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws DatabaseException
     */
    private function getClosedRequests(): HTTPResponse
    {
        return $this->returnRequests(TicketOperator::getClosedRequests($this->user));
    }

    /**
     * @param int $workspace
     * @param int $number
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws DatabaseException
     */
    private function getRequest(int $workspace, int $number): HTTPResponse
    {
        $ticket = TicketOperator::getRequest($this->user, $workspace, $number);
        $workspace = WorkspaceOperator::getWorkspace($ticket->getWorkspace());

        return new HTTPResponse(HTTPResponse::OK, array(
            'workspace' => $workspace->getId(),
            'workspaceName' => $workspace->getName(),
            'number' => $ticket->getNumber(),
            'title' => $ticket->getTitle(),
            'type' => AttributeOperator::nameFromId($ticket->getType()),
            'category' => AttributeOperator::nameFromId($ticket->getCategory()),
            'status' => TicketOperator::getTicketStatusName($ticket),
            'closureCodeName' => ($ticket->getClosureCode() == NULL) ? NULL : AttributeOperator::nameFromId((int)$ticket->getClosureCode()),
            'desiredDate' => $ticket->getDesiredDate(),
            'scheduledDate' => $ticket->getScheduledDate()
        ));
    }

    /**
     * @param int $workspace
     * @param int $number
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws DatabaseException
     */
    private function getUpdates(int $workspace, int $number): HTTPResponse
    {
        $ticket = TicketOperator::getRequest($this->user, $workspace, $number);

        $data = array();

        foreach($ticket->getUpdates() as $update)
        {
            $data[] = array(
                'time' => $update->getTime(),
                'description' => $update->getDescription()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws DatabaseException
     * @throws SecurityException
     * @throws ValidationError
     */
    private function newRequest(): HTTPResponse
    {
        $ticket = TicketOperator::createRequest(self::getFormattedBody(TicketOperator::FIELDS));

        return new HTTPResponse(HTTPResponse::CREATED, array('workspace' => $ticket->getWorkspace(), 'number' => $ticket->getNumber()));
    }

    /**
     * @param int $workspace
     * @param int $request
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws DatabaseException
     * @throws SecurityException
     * @throws ValidationError
     */
    private function updateRequest(int $workspace, int $request): HTTPResponse
    {
        $ticket = TicketOperator::getRequest($this->user, $workspace, $request);

        TicketOperator::updateRequest($ticket, self::getFormattedBody(TicketOperator::FIELDS)['description']);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param array $tickets
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws DatabaseException
     */
    private function returnRequests(array $tickets): HTTPResponse
    {
        $data = array();

        foreach($tickets as $ticket)
        {
            $workspace = WorkspaceOperator::getWorkspace($ticket->getWorkspace());

            $data[] = array(
                'workspace' => $workspace->getId(),
                'workspaceName' => $workspace->getName(),
                'number' => $ticket->getNumber(),
                'title' => $ticket->getTitle(),
                'type' => AttributeOperator::nameFromId($ticket->getType()),
                'category' => AttributeOperator::nameFromId($ticket->getCategory()),
                'status' => TicketOperator::getTicketStatusName($ticket),
                'updated' => TicketOperator::getTimeSince($ticket->getLastUpdateTime())
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string $type
     * @return HTTPResponse
     * @throws DatabaseException
     * @throws EntryNotFoundException
     */
    private function getAttributesOfType(string $type): HTTPResponse
    {
        $data = array();

        foreach (AttributeOperator::getAllOfType(WorkspaceOperator::getRequestPortal(), $type) as $attr)
        {
            $data[] = array(
                'id' => $attr->getId(),
                'type' => $attr->getType(),
                'code' => $attr->getCode(),
                'name' => $attr->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws DatabaseException
     */
    private function getAllAttributes(): HTTPResponse
    {
        $data = array();

        $attributeTypes = array('category', 'type');

        foreach($attributeTypes as $type)
        {
            $data[$type] = array();

            foreach (AttributeOperator::getAllOfType(WorkspaceOperator::getRequestPortal(), $type) as $attr)
            {
                $data[$type][] = array(
                    'id' => $attr->getId(),
                    'type' => $attr->getType(),
                    'code' => $attr->getCode(),
                    'name' => $attr->getName()
                );
            }
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }
}
