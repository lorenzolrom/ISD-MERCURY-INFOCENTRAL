<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/02/2020
 * Time: 7:31 PM
 */


namespace extensions\cliff\controllers;


use controllers\Controller;
use controllers\CurrentUserController;
use extensions\cliff\business\SystemOperator;
use models\HTTPRequest;
use models\HTTPResponse;

class SystemController extends Controller
{
    private const FIELDS = array('code', 'name');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        $p1 = $this->request->next();
        $p2 = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            if($p1 === NULL) //  GET ALL SYSTEMS
                return $this->getAllSystems();
            else if($p2 === NULL)
                return $this->getSystem((int)$p1);
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            if($p1 === NULL)
                return $this->createSystem();
            else if($p1 === 'search' AND $p2 === NULL)
                return $this->searchSystems();
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            if($p1 !== NULL AND $p2 === NULL)
                return $this->updateSystem((int)$p1);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            if($p1 !== NULL AND $p2 === NULL)
                return $this->deleteSystem((int)$p1);
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function getAllSystems(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-r');

        $systems = SystemOperator::getSearchResults(array('code' => '%', 'name' => '%'));

        return new HTTPResponse(HTTPResponse::OK, $systems);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function createSystem(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-w');

        return new HTTPResponse(HTTPResponse::CREATED, SystemOperator::create(self::getFormattedBody(self::FIELDS)));
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function updateSystem(int $id): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-w');

        $system = SystemOperator::get($id);
        SystemOperator::update($system, self::getFormattedBody(self::FIELDS));

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function deleteSystem(int $id): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-w');

        $system = SystemOperator::get($id);
        SystemOperator::delete($system);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function getSystem(int $id): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-r');

        $system = SystemOperator::get($id);

        return new HTTPResponse(HTTPResponse::OK, (array)$system);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function searchSystems(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-r');
        return new HTTPResponse(HTTPResponse::OK, SystemOperator::getSearchResults(self::getFormattedBody(self::FIELDS, FALSE)));
    }
}