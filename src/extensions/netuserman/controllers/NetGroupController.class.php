<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
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
     * @param string $cn
     * @return HTTPResponse
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\LDAPException
     */
    private function getGroup(string $cn): HTTPResponse
    {
        return new HTTPResponse(HTTPResponse::OK, NetGroupOperator::getGroupDetails(urldecode($cn)));
    }

    /**
     * @param string $cn
     * @return HTTPResponse
     * @throws LDAPException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function updateGroup(string $cn): HTTPResponse
    {
        NetGroupOperator::updateGroup(urldecode($cn), self::getFormattedBody(ExtConfig::OPTIONS['groupEditableAttributes']));
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string $cn
     * @return HTTPResponse
     * @throws LDAPException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function deleteGroup(string $cn): HTTPResponse
    {
        NetGroupOperator::deleteGroup(urldecode($cn));
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