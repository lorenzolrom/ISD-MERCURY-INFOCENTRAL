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
 * Time: 10:34 PM
 */


namespace extensions\itsm\controllers;


use business\AttributeOperator;
use extensions\itsm\business\ApplicationOperator;
use business\UserOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class ApplicationController extends Controller
{
    const FIELDS = array('number', 'name', 'description', 'owner', 'type', 'publicFacing', 'lifeExpectancy',
        'dataVolume', 'authType', 'port', 'host', 'vhost', 'status', 'webHosts', 'appHosts', 'dataHosts', 'vHosts');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     * @throws \exceptions\ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_ait-apps-r');

        $param = $this->request->next();

        if($this->request->method() == HTTPRequest::GET)
        {
            switch($param)
            {
                case 'types':
                    return $this->getTypes();
                case 'lifeExpectancies':
                    return $this->getLifeExpectancies();
                case 'dataVolumes':
                    return $this->getDataVolumes();
                case 'authTypes':
                    return $this->getAuthTypes();
                case 'statuses':
                    return $this->getStatuses();
                case null:
                    return $this->getSearchResult();
                default:
                    switch($this->request->next())
                    {
                        default:
                            return $this->getByNumber($param);
                    }
            }
        }
        else if($this->request->method() == HTTPRequest::POST)
        {
            switch($param)
            {
                case "search":
                    return $this->getSearchResult(TRUE);
                default:
                    return $this->createApplication();
            }
        }
        else if($this->request->method() === HTTPRequest::PUT)
            return $this->updateApplication($param);

        return NULL;
    }

    /**
     * @param string $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getByNumber(?string $param): HTTPResponse
    {
        $application = ApplicationOperator::getApplication((int)$param, TRUE);

        $data = array(
            'number' => $application->getNumber(),
            'name' => $application->getName(),
            'description' => $application->getDescription(),
            'owner' => UserOperator::usernameFromId($application->getOwner()),
            'type' => AttributeOperator::codeFromId($application->getType()),
            'typeName' => AttributeOperator::nameFromId($application->getType()),
            'status' => AttributeOperator::codeFromId($application->getStatus()),
            'statusName' => AttributeOperator::nameFromId($application->getStatus()),
            'publicFacing' => $application->getPublicFacing(),
            'lifeExpectancy' => AttributeOperator::codeFromId($application->getLifeExpectancy()),
            'lifeExpectancyName' => AttributeOperator::nameFromId($application->getLifeExpectancy()),
            'dataVolume' => AttributeOperator::codeFromId($application->getDataVolume()),
            'dataVolumeName' => AttributeOperator::nameFromId($application->getDataVolume()),
            'authType' => AttributeOperator::codeFromId($application->getAuthType()),
            'authTypeName' => AttributeOperator::nameFromId($application->getAuthType()),
            'port' => $application->getPort(),
            'appHosts' => array(),
            'webHosts' => array(),
            'dataHosts' => array(),
            'vHosts' => array()
        );

        // Get app hosts
        foreach(ApplicationOperator::getAppHosts($application) as $host)
        {
            $data['appHosts'][] = array(
                'id' => $host->getId(),
                'systemName' => $host->getSystemName(),
                'ipAddress' => $host->getIpAddress()
            );
        }

        // Get web hosts
        foreach(ApplicationOperator::getWebHosts($application) as $host)
        {
            $data['webHosts'][] = array(
                'id' => $host->getId(),
                'systemName' => $host->getSystemName(),
                'ipAddress' => $host->getIpAddress()
            );
        }

        // Get data hosts
        foreach(ApplicationOperator::getDataHosts($application) as $host)
        {
            $data['dataHosts'][] = array(
                'id' => $host->getId(),
                'systemName' => $host->getSystemName(),
                'ipAddress' => $host->getIpAddress()
            );
        }

        // Get vhosts
        foreach(ApplicationOperator::getVHosts($application) as $vhost)
        {
            $data['vHosts'][] = array(
                'id' => $vhost->getId(),
                'domain' => $vhost->getDomain(),
                'subdomain' => $vhost->getSubdomain()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param bool $search
     * @param bool $strict
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     */
    private function getSearchResult(bool $search = FALSE, bool $strict = FALSE): HTTPResponse
    {
        if($search)
        {
            $args = $this->getFormattedBody(self::FIELDS, $strict);

            $apps = ApplicationOperator::search($args['number'], $args['name'], $args['description'], $args['owner'], $args['type'], $args['publicFacing'],
                                                        $args['lifeExpectancy'], $args['dataVolume'], $args['authType'], $args['port'], $args['host'], $args['vhost'], $args['status']);
        }
        else
            $apps = ApplicationOperator::search();

        $results = array();

        foreach($apps as $app)
        {
            $results[] = array(
                'number' => $app->getNumber(),
                'name' => $app->getName(),
                'type' => AttributeOperator::nameFromId($app->getType()),
                'status' => AttributeOperator::nameFromId($app->getStatus()),
                'owner' => UserOperator::usernameFromId($app->getOwner())
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $results);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getTypes(): HTTPResponse
    {
        $data = array();

        foreach(ApplicationOperator::getTypes() as $attr)
        {
            $data[] = array(
                'id' => $attr->getId(),
                'code' => $attr->getCode(),
                'name' => $attr->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getLifeExpectancies(): HTTPResponse
    {
        $data = array();

        foreach(ApplicationOperator::getLifeExpectancies() as $attr)
        {
            $data[] = array(
                'id' => $attr->getId(),
                'code' => $attr->getCode(),
                'name' => $attr->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getDataVolumes(): HTTPResponse
    {
        $data = array();

        foreach(ApplicationOperator::getDataVolumes() as $attr)
        {
            $data[] = array(
                'id' => $attr->getId(),
                'code' => $attr->getCode(),
                'name' => $attr->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getAuthTypes(): HTTPResponse
    {
        $data = array();

        foreach(ApplicationOperator::getAuthTypes() as $attr)
        {
            $data[] = array(
                'id' => $attr->getId(),
                'code' => $attr->getCode(),
                'name' => $attr->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getStatuses(): HTTPResponse
    {
        $data = array();

        foreach(ApplicationOperator::getStatuses() as $attr)
        {
            $data[] = array(
                'id' => $attr->getId(),
                'code' => $attr->getCode(),
                'name' => $attr->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function createApplication(): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_ait-apps-w');

        $args = self::getFormattedBody(self::FIELDS);

        return new HTTPResponse(HTTPResponse::CREATED, ApplicationOperator::createApplication($args));
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function updateApplication(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_ait-apps-w');

        $app = ApplicationOperator::getApplication((int) $param, TRUE);
        $args = self::getFormattedBody(self::FIELDS);
        ApplicationOperator::updateApplication($app, $args);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}
