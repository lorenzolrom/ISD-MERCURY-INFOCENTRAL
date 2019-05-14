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
use exceptions\EntryInUseException;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class WorkspaceController extends Controller
{
    private const FIELDS = array('name', 'teams');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     */
    public function getResponse(): ?HTTPResponse
    {
        $param = $this->request->next();
        $subject = $this->request->next();

        if($param !== NULL AND $subject == 'attributes')
        {
            $a = new AttributeController($param, $this->request);
            return $a->getResponse();
        }

        if($this->request->method() === HTTPRequest::GET)
        {
            if($param === NULL)
                return $this->getAll();
            return $this->getWorkspace($param);
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
}