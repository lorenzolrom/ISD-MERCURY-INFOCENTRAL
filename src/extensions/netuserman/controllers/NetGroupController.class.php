<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 11/02/2019
 * Time: 2:06 PM
 */


namespace extensions\netuserman\controllers;


use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\LDAPException;
use extensions\netuserman\business\NetGroupOperator;
use extensions\netuserman\ExtConfig;
use models\HTTPRequest;
use models\HTTPResponse;
use utilities\LDAPConnection;
use utilities\LDAPUtility;

class NetGroupController extends Controller
{

    /**
     * @return HTTPResponse|null
     * @throws LDAPException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('netuserman-readgroups');
        $param = $this->request->next();
        $next = $this->request->next();

        if($this->request->method() === HTTPRequest::POST)
        {
            if($param === 'search' AND $next === NULL)
            {
                return $this->searchGroups();
            }
            else if($param === NULL)
            {
                CurrentUserController::validatePermission('netuserman-creategroups');
                return $this->createGroup();
            }
        }
        else if($this->request->method() === HTTPRequest::GET)
        {
            if($next === NULL AND $param !== NULL)
                return $this->getGroup((string)$param);
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            CurrentUserController::validatePermission('netuserman-editgroups');
            if($next === NULL AND $param !== NULL)
                return $this->updateGroup((string)$param);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            CurrentUserController::validatePermission('netuserman-deletegroups');
            if($next === NULL AND $param !== NULL)
                return $this->deleteGroup((string)$param);
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\LDAPException
     */
    private function searchGroups(): HTTPResponse
    {
        $results = NetGroupOperator::searchGroups(self::getFormattedBody(ExtConfig::OPTIONS['groupSearchByAttributes'], TRUE));

        return new HTTPResponse(HTTPResponse::OK, $results);
    }

    /**
     * @param string $guid
     * @return HTTPResponse
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\LDAPException
     */
    private function getGroup(string $guid): HTTPResponse
    {
        $cn = LDAPUtility::guidToCN($guid);
        return new HTTPResponse(HTTPResponse::OK, NetGroupOperator::getGroupDetails($cn));
    }

    /**
     * @param string $guid
     * @return HTTPResponse
     * @throws LDAPException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function updateGroup(string $guid): HTTPResponse
    {
        $cn = LDAPUtility::guidToCN($guid);
        NetGroupOperator::updateGroup($cn, self::getFormattedBody(ExtConfig::OPTIONS['groupEditableAttributes']));
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string $guid
     * @return HTTPResponse
     * @throws LDAPException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function deleteGroup(string $guid): HTTPResponse
    {
        $cn = LDAPUtility::guidToCN($guid);
        NetGroupOperator::deleteGroup($cn);
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @return HTTPResponse
     * @throws LDAPException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function createGroup(): HTTPResponse
    {
        $result = NetGroupOperator::createGroup(self::getFormattedBody(ExtConfig::OPTIONS['groupEditableAttributes'], TRUE));
        return new HTTPResponse(HTTPResponse::CREATED, $result);
    }
}
