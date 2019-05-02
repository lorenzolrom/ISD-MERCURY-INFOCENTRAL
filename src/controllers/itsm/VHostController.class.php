<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 5:25 PM
 */


namespace controllers\itsm;


use business\AttributeOperator;
use business\itsm\HostOperator;
use business\itsm\RegistrarOperator;
use business\itsm\VHostOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class VHostController extends Controller
{
    private const SEARCH_FIELDS = array('domain', 'subdomain', 'name', 'host', 'registrarCode', 'status');
    private const FIELDS = array('domain', 'subdomain', 'name', 'host', 'status', 'registrar', 'notes', 'registerDate', 'expireDate');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_web-vhosts-r');

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
                    return $this->getById($param);
            }
        }
        else if($this->request->method() == HTTPRequest::POST)
        {
            switch($param)
            {
                case "search":
                    return $this->getSearchResult(TRUE);
            }
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
            'registrar' => $vhost->getRegistrar(),
            'status' => $vhost->getStatus(),
            'renewCost' => $vhost->getRenewCost(),
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

            $vhosts = VHostOperator::search($args['domain'], $args['subdomain'], $args['name'], $args['host'], $args['registrarCode'], $args['status']);
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
                'status' => $vhost->getStatus(),
                'statusName' => AttributeOperator::nameFromId($vhost->getStatus()),
                'host' => $vhost->getHost(),
                'hostName' => HostOperator::getDisplayNameById($vhost->getHost())
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $results);
    }
}