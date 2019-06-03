<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/14/2019
 * Time: 9:33 AM
 */


namespace controllers\tickets;


use business\tickets\TeamOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use models\HTTPRequest;
use models\HTTPResponse;

class TeamController extends Controller
{
    private const FIELDS = array('name', 'users');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('tickets-admin');
        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            if($param === NULL)
                return $this->getAll();
            return $this->getTeam($param);
        }
        else if($this->request->method() === HTTPRequest::POST)
            return $this->createTeam();
        else if($this->request->method() === HTTPRequest::PUT)
            return $this->updateTeam($param);
        else if($this->request->method() === HTTPRequest::DELETE)
            return $this->deleteTeam($param);

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getAll(): HTTPResponse
    {
        $data = array();

        foreach(TeamOperator::getAll() as $team)
        {
            $data[] = array(
                'id' => $team->getId(),
                'name' => $team->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getTeam(?string $param): HTTPResponse
    {
        $team = TeamOperator::getTeam((int) $param);
        $users = array();

        foreach($team->getUsers() as $user)
        {
            $users[] = array(
                'id' => $user->getId(),
                'username' => $user->getUsername()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, array(
            'id' => $team->getId(),
            'name' => $team->getName(),
            'users' => $users
        ));
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function createTeam(): HTTPResponse
    {
        return new HTTPResponse(HTTPResponse::CREATED, TeamOperator::create(self::getFormattedBody(self::FIELDS)));
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function updateTeam(?string $param): HTTPResponse
    {
        $team = TeamOperator::getTeam((int) $param);
        TeamOperator::update($team, self::getFormattedBody(self::FIELDS));

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function deleteTeam(?string $param): HTTPResponse
    {
        $team = TeamOperator::getTeam((int) $param);
        TeamOperator::delete($team);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}