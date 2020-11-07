<?php


namespace extensions\itsm\controllers;


use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use extensions\itsm\business\WebServerOperator;
use models\HTTPRequest;
use models\HTTPResponse;

class WebServerController extends Controller
{
    private const INSERT_FIELDS = array('host', 'webroot', 'logpath');
    private const UPDATE_FIELDS = array('webroot', 'logpath');
    private const SEARCH_FIELDS = array('ipAddress', 'systemName');

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

        if($this->request->method() === HTTPRequest::GET)
        {
            if($p1 === NULL) // Get all
                return $this->getAllWebServers();
            else if($p2 === NULL)
                return $this->getWebServer((int)$p1);
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            if($p1 === NULL)
                return $this->postWebServer();
            else if($p1 === 'search' AND $p2 === NULL)
                return $this->searchWebServers();
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            if($p1 !== NULL AND $p2 === NULL)
                return $this->putWebServer((int)$p1);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            if($p1 !== NULL AND $p2 === NULL)
                return $this->deleteWebServer((int)$p1);
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function getAllWebServers(): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_web-servers-r', 'itsm_web-vhosts-r'));

        $servers = WebServerOperator::getSearchResults(array('systemName' => '%', 'ipAddress' => '%'));
        return new HTTPResponse(HTTPResponse::OK, $servers);
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function postWebServer(): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_web-servers-w');

        return new HTTPResponse(HTTPResponse::CREATED, WebServerOperator::create(self::getFormattedBody(self::INSERT_FIELDS)));
    }

    /**
     * @param int $host
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function putWebServer(int $host): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_web-servers-w');

        $ws = WebServerOperator::get($host);
        WebServerOperator::update($ws, self::getFormattedBody(self::UPDATE_FIELDS));

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param int $host
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function deleteWebServer(int $host): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_web-servers-w');

        $ws = WebServerOperator::get($host);
        WebServerOperator::delete($ws);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param int $host
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function getWebServer(int $host): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_web-servers-r', 'itsm_web-vhosts-r'));

        $ws = WebServerOperator::get($host);

        return new HTTPResponse(HTTPResponse::OK, (array)$ws);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function searchWebServers(): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_web-servers-r', 'itsm_web-vhosts-r'));
        return new HTTPResponse(HTTPResponse::OK, WebServerOperator::getSearchResults(self::getFormattedBody(self::SEARCH_FIELDS, FALSE)));
    }
}