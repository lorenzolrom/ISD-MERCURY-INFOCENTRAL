<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 5:25 PM
 */


namespace extensions\itsm\controllers;


use business\AttributeOperator;
use extensions\itsm\business\HostOperator;
use extensions\itsm\business\RegistrarOperator;
use extensions\itsm\business\VHostOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;
use extensions\itsm\utilities\WebLogFileRetriever;

class VHostController extends Controller
{
    private const SEARCH_FIELDS = array('domain', 'subdomain', 'name', 'host', 'registrar', 'status');
    private const FIELDS = array('domain', 'subdomain', 'name', 'host', 'status', 'registrar', 'renewCost', 'notes',
        'webRoot', 'logPath', 'registerDate', 'expireDate');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\EntryInUseException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_web-vhosts-r', 'itsm_ait-apps-w'));

        $param = $this->request->next();

        if($this->request->method() == HTTPRequest::GET)
        {
            switch ($param)
            {
                case 'statuses':
                    return $this->getStatuses();
                case null:
                    return $this->getSearchResult();
                default:
                    switch($this->request->next())
                    {
                        case 'logs':
                            $logName = $this->request->next();

                            if($logName !== NULL)
                                return $this->getVHostLog($param, $logName);

                            return $this->getVHostLogs($param);
                        default:
                            return $this->getById($param);
                    }
            }
        }
        else if($this->request->method() == HTTPRequest::POST)
        {
            switch($param)
            {
                case "search":
                    return $this->getSearchResult(TRUE);
                case null:
                    return $this->createVHost();
            }
        }
        else if($this->request->method() == HTTPRequest::PUT)
        {
            return $this->updateVHost($param);
        }
        else if($this->request->method() == HTTPRequest::DELETE)
        {
            return $this->deleteVHost($param);
        }

        return NULL;
    }

    /**
     * @param string $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getById(string $param): HTTPResponse
    {
        $vhost = VHostOperator::getVHost((int)$param);

        $data = array(
            'id' => $vhost->getId(),
            'subdomain' => $vhost->getSubdomain(),
            'domain' => $vhost->getDomain(),
            'name' => $vhost->getName(),
            'host' => $vhost->getHost(),
            'ipAddress' => HostOperator::getIPAddressById($vhost->getHost()),
            'registrar' => $vhost->getRegistrar(),
            'registrarCode' => RegistrarOperator::codeFromId($vhost->getRegistrar()),
            'registrarName' => RegistrarOperator::nameFromId($vhost->getRegistrar()),
            'status' => AttributeOperator::codeFromId($vhost->getStatus()),
            'renewCost' => $vhost->getRenewCost(),
            'webRoot' => $vhost->getWebRoot(),
            'logPath' => $vhost->getLogPath(),
            'notes' => $vhost->getNotes(),
            'registerDate' => $vhost->getRegisterDate(),
            'expireDate' => $vhost->getExpireDate()
        );

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getStatuses(): HTTPResponse
    {
        $statuses = VHostOperator::getStatuses();

        $data = array();

        foreach($statuses as $status)
        {
            $data[] = array(
                'id' => $status->getId(),
                'code' => $status->getCode(),
                'name' => $status->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param bool $search Should this be performed as a search using the request body?
     * @param bool $strict Should the search match query params exactly, or use wildcards?
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     */
    private function getSearchResult(bool $search = FALSE, bool $strict = FALSE): HTTPResponse
    {
        if($search)
        {
            $args = $this->getFormattedBody(self::SEARCH_FIELDS, $strict);

            $vhosts = VHostOperator::search($args['domain'], $args['subdomain'], $args['name'], $args['host'], $args['registrar'], $args['status']);
        }
        else
            $vhosts = VHostOperator::search();

        $results = array();

        foreach ($vhosts as $vhost)
        {
            $results[] = array(
                'id' => $vhost->getId(),
                'subdomain' => $vhost->getSubdomain(),
                'domain' => $vhost->getDomain(),
                'name' => $vhost->getName(),
                'registrar' => $vhost->getRegistrar(),
                'registrarName' => RegistrarOperator::nameFromId($vhost->getRegistrar()),
                'status' => AttributeOperator::codeFromId($vhost->getStatus()),
                'statusName' => AttributeOperator::nameFromId($vhost->getStatus()),
                'host' => $vhost->getHost(),
                'hostName' => HostOperator::getDisplayNameById($vhost->getHost())
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $results);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws EntryNotFoundException
     */
    private function createVHost(): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_web-vhosts-w'));

        $args = $this->getFormattedBody(self::FIELDS, TRUE);

        $errors = VHostOperator::createVHost($args['subdomain'], $args['domain'], $args['name'], $args['host'],
            $args['registrar'], $args['status'], $args['renewCost'], $args['registerDate'], $args['expireDate'],
            $args['notes'], $args['webRoot'], $args['logPath']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::CREATED, $errors);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function updateVHost(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_web-vhosts-w'));

        $vhost = VHostOperator::getVHost((int) $param);

        $args = $this->getFormattedBody(self::FIELDS, TRUE);

        $errors = VHostOperator::updateVHost($vhost, $args['subdomain'], $args['domain'], $args['name'], $args['host'],
            $args['registrar'], $args['status'], $args['renewCost'], $args['registerDate'], $args['expireDate'],
            $args['notes'], $args['webRoot'], $args['logPath']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT, $errors);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryInUseException
     * @throws \exceptions\SecurityException
     */
    private function deleteVHost(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_web-vhosts-w'));

        $vhost = VHostOperator::getVHost((int) $param);

        VHostOperator::deleteVHost($vhost);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function getVHostLogs(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_weblogs'));

        $vhost = VHostOperator::getVHost((int) $param);

        return new HTTPResponse(HTTPResponse::OK, VHostOperator::getLogFiles($vhost));
    }

    /**
     * @param string|null $id
     * @param string|null $logName
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function getVHostLog(?string $id, ?string $logName): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_weblogs'));

        $vhost = VHostOperator::getVHost((int) $id);

        $logContents = WebLogFileRetriever::getLogContents($vhost->getLogPath() . '/' . $logName);

        if($logContents === NULL)
            return new HTTPResponse(HTTPResponse::NOT_FOUND, array('errors' => array('Log not found')));

        return new HTTPResponse(HTTPResponse::OK, array($logContents));
    }
}
