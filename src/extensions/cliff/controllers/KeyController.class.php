<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
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
use extensions\cliff\business\KeyOperator;
use models\HTTPRequest;
use models\HTTPResponse;

class KeyController extends Controller
{
    private const FIELDS = array('systemCode', 'stamp', 'bitting', 'type', 'keyway', 'notes');

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

        if($p1 !== NULL AND $p2 === 'issues') // Redirect to Issue controller
        {
            $key = KeyOperator::get((int)$p1);
            $kic = new KeyIssueController($key, $this->request);
            return $kic->getResponse();
        }

        if($this->request->method() === HTTPRequest::GET)
        {
            if($p1 === NULL)
                return $this->getAllKeys();
            else if($p2 === NULL)
                return $this->getKey((int)$p1);
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            if($p1 === NULL)
                return $this->createKey();
            else if($p1 === 'search' AND $p2 === NULL)
                return $this->searchKeys();
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            if($p1 !== NULL AND $p2 === NULL)
                return $this->updateKey((int)$p1);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            if($p1 !== NULL AND $p2 === NULL)
                return $this->deleteKey((int)$p1);
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function getAllKeys(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-r');

        $keys = KeyOperator::getSearchResults(array(
            'systemCode' => '%',
            'stamp' => '%',
            'bitting' => '%',
            'type' => '%',
            'keyway' => '%',
            'notes' => '%'
        ));

        return new HTTPResponse(HTTPResponse::OK, $keys);
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function getKey(int $id): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-r');
        $key = KeyOperator::get($id);
        return new HTTPResponse(HTTPResponse::OK, (array)$key);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function searchKeys(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-r');
        return new HTTPResponse(HTTPResponse::OK, KeyOperator::getSearchResults(self::getFormattedBody(self::FIELDS, FALSE)));
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function createKey(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-w');
        return new HTTPResponse(HTTPResponse::CREATED, KeyOperator::create(self::getFormattedBody(self::FIELDS)));
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function updateKey(int $id): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-w');
        $key = KeyOperator::get($id);
        KeyOperator::update($key, self::getFormattedBody(self::FIELDS));

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function deleteKey(int $id): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-w');
        $key = KeyOperator::get($id);
        KeyOperator::delete($key);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}
