<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/12/2020
 * Time: 3:06 PM
 */


namespace extensions\cliff\controllers;


use controllers\Controller;
use controllers\CurrentUserController;
use extensions\cliff\business\KeyOperator;
use extensions\cliff\models\Key;
use models\HTTPRequest;
use models\HTTPResponse;

class KeyIssueController extends Controller
{
    private const CREATE_ISSUE_FIELDS = array('serial', 'issuedTo');
    private const EDIT_ISSUE_FIELDS = array('issuedTo');

    private $key;

    public function __construct(Key $key, HTTPRequest $request)
    {
        $this->key = $key;
        parent::__construct($request);
    }

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        $p1 = $this->request->next(); // Optional ID of issue resource

        if($this->request->method() === HTTPRequest::GET)
        {
            if($p1 === NULL)
                return $this->getAllIssues();
            else
                return $this->getIssue((int)$p1);
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            if($p1 === NULL)
                return $this->createIssue();
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            if($p1 !== NULL)
                return $this->updateIssue((int)$p1);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            if($p1 !== NULL)
                return $this->deleteIssue((int)$p1);
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function getAllIssues(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-r');
        return new HTTPResponse(HTTPResponse::OK, KeyOperator::getKeyIssues($this->key));
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function getIssue(int $id): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-r');
        return new HTTPResponse(HTTPResponse::OK, (array)KeyOperator::getKeyIssue($id));
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function createIssue(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-w');
        return new HTTPResponse(HTTPResponse::CREATED, array('id' => KeyOperator::issueKey($this->key, self::getFormattedBody(self::CREATE_ISSUE_FIELDS))->getId()));
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function updateIssue(int $id): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-w');
        $issue = KeyOperator::getKeyIssue($id);
        KeyOperator::updateIssue($issue, (string)self::getFormattedBody(self::EDIT_ISSUE_FIELDS)['issuedTo']);
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function deleteIssue(int $id): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-w');
        $issue = KeyOperator::getKeyIssue($id);
        KeyOperator::deleteIssue($issue);
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}