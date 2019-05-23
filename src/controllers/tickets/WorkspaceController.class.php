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
        else if($this->request->method() === HTTPRequest::GET)
        {
            if($param === NULL)
                return $this->getAll();
            return $this->getWorkspace($param);
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            return $this->createWorkspace();
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
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
     * @throws \exceptions\DatabaseException
     */
    private function getAll(): HTTPResponse
    {
        $data = array();

        foreach(WorkspaceOperator::getAll() as $workspace)
        {
            $data[] = array(
                'id' => $workspace->getId(),
                'name' => $workspace->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getWorkspace(?string $param): HTTPResponse
    {
        $workspace = WorkspaceOperator::getWorkspace((int) $param);
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
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function createWorkspace(): HTTPResponse
    {
        CurrentUserController::validatePermission('tickets-admin');
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
        CurrentUserController::validatePermission('tickets-admin');
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
        CurrentUserController::validatePermission('tickets-admin');
        $workspace = WorkspaceOperator::getWorkspace((int)$param);
        WorkspaceOperator::delete($workspace);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}