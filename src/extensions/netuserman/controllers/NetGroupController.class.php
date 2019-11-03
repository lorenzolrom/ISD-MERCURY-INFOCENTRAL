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
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\LDAPException
     * @throws \exceptions\SecurityException
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
        }
        else if($this->request->method() === HTTPRequest::GET)
        {
            if($next === NULL AND $param !== NULL)
                return $this->getGroup((string)$param);
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            if($next === NULL AND $param !== NULL)
                return $this->updateGroup((string)$param);
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\LDAPException
     */
    private function searchGroups(): HTTPResponse
    {
        $results = NetGroupOperator::searchGroups(self::getFormattedBody(ExtConfig::OPTIONS['allowedSearchGroupAttributes'], TRUE));

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
        return new HTTPResponse(HTTPResponse::OK, NetGroupOperator::getGroupDetails($cn, ExtConfig::OPTIONS['usedGroupAttributes']));
    }

    /**
     * @param string $cn
     * @return HTTPResponse
     * @throws \exceptions\LDAPException
     */
    private function updateGroup(string $cn): HTTPResponse
    {
        if(NetGroupOperator::updateGroup($cn, self::getFormattedBody(ExtConfig::OPTIONS['usedGroupAttributes'])))
            return new HTTPResponse(HTTPResponse::NO_CONTENT);

        throw new LDAPException(LDAPException::MESSAGES[LDAPException::OPERATION_FAILED], LDAPException::OPERATION_FAILED);
    }
}