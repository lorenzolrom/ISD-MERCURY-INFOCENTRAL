<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/14/2019
 * Time: 9:32 AM
 */


namespace controllers\tickets;


use business\tickets\WorkspaceOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryInUseException;
use exceptions\EntryNotFoundException;
use exceptions\SecurityException;
use models\HTTPRequest;
use models\HTTPResponse;

class WorkspaceController extends Controller
{
    private const FIELDS = array('name', 'teams');

    /**
     * @return HTTPResponse|null
     * @throws EntryInUseException
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('tickets');
        $param = $this->request->next();
        $subject = $this->request->next();

        if($param !== NULL AND $subject == 'attributes')
        {
            $a = new AttributeController($param, $this->request);
            return $a->getResponse();
        }
        else if($param !== NULL AND $subject == 'tickets')
        {
            $t = new TicketController($param, $this->request);
            return $t->getResponse();
        }

        if($this->request->method() === HTTPRequest::GET)
        {
            if($param === 'requestPortal')
                return $this->getRequestPortal();

            CurrentUserController::validatePermission('tickets-agent');

            if($param === NULL)
                return $this->getAll();

            return $this->getWorkspace($param);
        }

        CurrentUserController::validatePermission('tickets-admin');

        if($this->request->method() === HTTPRequest::POST)
        {
            return $this->createWorkspace();
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            if($subject == 'requestPortal')
                return $this->setRequestPortal($param);

            return $this->updateWorkspace($param);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            return $this->deleteWorkspace($param);
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     */
    private function getAll(): HTTPResponse
    {
        $showAll = TRUE;

        // Only show workspace memberships if the user does not have admin permission
        try{CurrentUserController::validatePermission('tickets-admin');}
        catch(SecurityException $e){$showAll = FALSE;}

        $data = array();

        foreach(WorkspaceOperator::getAll() as $workspace)
        {
            if(!$showAll AND !WorkspaceOperator::currentUserInWorkspace($workspace)) // Skip workspace if user does not have 'admin' and is not a member of the workspace
                continue;

            $data[] = array(
                'id' => $workspace->getId(),
                'name' => $workspace->getName(),
                'requestPortal' => $workspace->getRequestPortal()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws SecurityException
     */
    private function getWorkspace(?string $param): HTTPResponse
    {
        $workspace = WorkspaceOperator::getWorkspace((int) $param);

        try{CurrentUserController::validatePermission('tickets-admin');}
        catch(SecurityException $e)
        {
            if(!WorkspaceOperator::currentUserInWorkspace($workspace))
                throw new SecurityException('You are not allowed to view this workspace', SecurityException::USER_NO_PERMISSION);
        }

        $teams = array();

        foreach($workspace->getTeams() as $team)
        {
            $teams[] = array(
                'id' => $team->getId(),
                'name' => $team->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, array(
            'id' => $workspace->getId(),
            'name' => $workspace->getName(),
            'teams' => $teams
        ));
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getRequestPortal(): HTTPResponse
    {
        $workspace = WorkspaceOperator::getRequestPortal();

        return new HTTPResponse(HTTPResponse::OK, array(
            'id' => $workspace->getId(),
            'name' => $workspace->getName()
        ));
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function createWorkspace(): HTTPResponse
    {
        return new HTTPResponse(HTTPResponse::CREATED, WorkspaceOperator::create(self::getFormattedBody(self::FIELDS)));
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function updateWorkspace(?string $param): HTTPResponse
    {
        $workspace = WorkspaceOperator::getWorkspace((int)$param);
        WorkspaceOperator::update($workspace, self::getFormattedBody(self::FIELDS));

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws EntryInUseException
     */
    private function deleteWorkspace(?string $param): HTTPResponse
    {
        $workspace = WorkspaceOperator::getWorkspace((int)$param);
        WorkspaceOperator::delete($workspace);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function setRequestPortal(?string $param): HTTPResponse
    {
        $workspace = WorkspaceOperator::getWorkspace((int)$param);

        WorkspaceOperator::setRequestPortal($workspace);
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}