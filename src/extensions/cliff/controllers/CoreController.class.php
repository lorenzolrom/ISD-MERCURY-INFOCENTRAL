<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/11/2020
 * Time: 5:40 PM
 */


namespace extensions\cliff\controllers;


use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use extensions\cliff\business\CoreOperator;
use models\HTTPRequest;
use models\HTTPResponse;

class CoreController extends Controller
{
    private const FIELDS = array('systemCode', 'stamp', 'pinData', 'type', 'keyway', 'notes');

    /**
     * @return HTTPResponse|null
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        $p1 = $this->request->next();
        $p2 = $this->request->next();

        if($p1 !== NULL AND $p2 === 'locations') // Redirect to Location controller
        {
            $core = CoreOperator::get((int)$p1);
            $clc = new CoreLocationController($core, $this->request);
            return $clc->getResponse();
        }

        if($this->request->method() === HTTPRequest::GET)
        {
            if($p1 === NULL)
                return $this->getAllCores();
            else if($p2 === NULL)
                return $this->getCore((int)$p1);
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            if($p1 === NULL)
                return $this->createCore();
            else if($p1 === 'search' AND $p2 === NULL)
                return $this->searchCore();
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            if($p1 !== NULL AND $p2 === NULL)
                return $this->updateCore((int)$p1);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            if($p1 !== NULL AND $p2 === NULL)
                return $this->deleteCore((int)$p1);
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function getAllCores(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-r');

        $cores = CoreOperator::getSearchResults(array(
            'systemCode' => '%',
            'stamp' => '%',
            'type' => '%',
            'keyway' => '%',
            'notes' => '%'
        ));

        return new HTTPResponse(HTTPResponse::OK, $cores);
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function getCore(int $id): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-r');
        $core = CoreOperator::get($id);
        return new HTTPResponse(HTTPResponse::OK, (array)$core);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function searchCore(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-r');
        return new HTTPResponse(HTTPResponse::OK, CoreOperator::getSearchResults(self::getFormattedBody(self::FIELDS, FALSE)));
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function createCore(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-w');
        return new HTTPResponse(HTTPResponse::CREATED, CoreOperator::create(self::getFormattedBody(self::FIELDS)));
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function updateCore(int $id): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-w');
        $core = CoreOperator::get($id);
        CoreOperator::update($core, self::getFormattedBody(self::FIELDS));

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function deleteCore(int $id): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-w');
        $core = CoreOperator::get($id);
        CoreOperator::delete($core);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}