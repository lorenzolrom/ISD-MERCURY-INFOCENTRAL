<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/14/2019
 * Time: 9:32 AM
 */


namespace extensions\tickets\controllers;


use business\SecretOperator;
use exceptions\ValidationError;
use extensions\tickets\business\WorkspaceOperator;
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
     * @throws \exceptions\EntryIsBusyException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('tickets'); // TICKET USERS
        $param = $this->request->next();
        $subject = $this->request->next();

        if($param !== NULL)
        {
            if($subject == 'attributes')
            {
                $a = new AttributeController($param, $this->request);
                return $a->getResponse();
            }
            else if($subject == 'tickets')
            {
                CurrentUserController::validatePermission('tickets-agent'); // ALL AGENTS
                $t = new TicketController($param, $this->request);
                return $t->getResponse();
            }
            else if($subject == 'searches')
            {
                CurrentUserController::validatePermission('tickets-agent'); // ALL AGENTS
                $s = new SearchController($param, $this->request);
                return $s->getResponse();
            }
            else if($subject == 'widgets')
            {
                CurrentUserController::validatePermission('tickets-agent'); // ALL AGENTS
                $w = new WidgetController($param, $this->request);
                return $w->getResponse();
            }
        }

        if($this->request->method() === HTTPRequest::GET)
        {
            if($param === 'requestPortal')
                return $this->getRequestPortal();

            CurrentUserController::validatePermission('tickets-agent'); // ALL AGENTS

            if($param === NULL)
                return $this->getAll();
            else if($param === 'all')
                return $this->getAll(TRUE);

            if($subject == 'assignees')
                return $this->getAssignees($param);

            return $this->getWorkspace($param);
        }

        if($this->request->method() === HTTPRequest::POST) // ADMIN
        {
            CurrentUserController::validatePermission('tickets-admin');

            return $this->createWorkspace();
        }
        else if($this->request->method() === HTTPRequest::PUT) // ADMIN
        {
            CurrentUserController::validatePermission('tickets-admin');

            if($subject == 'requestPortal')
                return $this->setRequestPortal($param);

            return $this->updateWorkspace($param);
        }
        else if($this->request->method() === HTTPRequest::DELETE) // ADMIN
        {
            CurrentUserController::validatePermission('tickets-admin');

            return $this->deleteWorkspace($param);
        }

        return NULL;
    }

    /**
     * @param bool $showAll
     * @return HTTPResponse
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     */
    private function getAll(bool $showAll = FALSE): HTTPResponse
    {
        // Only show workspace memberships if the user does not have admin permission
        try
        {
            CurrentUserController::validatePermission('tickets-admin');
        }
        catch(SecurityException $e)
        {
            $showAll = FALSE;
        }

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

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getAssignees(?string $param): HTTPResponse
    {
        $workspace = WorkspaceOperator::getWorkspace((int)$param);

        return new HTTPResponse(HTTPResponse::OK, WorkspaceOperator::getAssigneeList($workspace));
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getAllowedSecrets(?string $param): HTTPResponse
    {
        $workspace = WorkspaceOperator::getWorkspace((int)$param);

        $secrets = array();

        foreach($workspace->getAllowedSecrets() as $secret)
        {
            $secrets[] = array(
                'id' => $secret->getId(),
                'name' => $secret->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $secrets);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     */
    private function addAllowedSecret(?string $param): HTTPResponse
    {
        $secretID = self::getFormattedBody(array('secretID'))['secretID'];
        $workspace = WorkspaceOperator::getWorkspace((int)$param);

        try
        {
            $secret = SecretOperator::getSecretById($secretID);
            WorkspaceOperator::addSecret($workspace, $secret);
            return new HTTPResponse(HTTPResponse::CREATED);
        }
        catch(EntryNotFoundException $e)
        {
            throw new ValidationError(array('Secret Is Not Valid'));
        }
    }

    /**
     * @param string|null $workspaceID
     * @param string|null $secretID
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     */
    private function removeAllowedSecret(?string $workspaceID, ?string $secretID)
    {
        $workspace = WorkspaceOperator::getWorkspace((int)$workspaceID);
        $secret = SecretOperator::getSecretById((int)$secretID);

        WorkspaceOperator::delSecret($workspace, $secret);
    }
}
