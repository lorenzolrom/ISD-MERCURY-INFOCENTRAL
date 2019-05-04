<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 10:34 PM
 */


namespace controllers\itsm;


use business\AttributeOperator;
use business\itsm\ApplicationOperator;
use business\UserOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use database\itsm\ApplicationUpdateDatabaseHandler;
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
                        case 'updates':
                            return $this->getUpdates($param);
                        case 'lastUpdate':
                            return $this->getLastUpdate($param);
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
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getUpdates(?string $param): HTTPResponse
    {
        $app = ApplicationOperator::getApplication((int) $param, TRUE);

        $data = array();

        foreach(ApplicationUpdateDatabaseHandler::selectByApplication($app->getId()) as $update)
        {
            $data[] = array(
                'status' => AttributeOperator::nameFromId($update->getStatus()),
                'time' => $update->getTime(),
                'user' => UserOperator::usernameFromId($update->getUser()),
                'description' => $update->getDescription()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getLastUpdate(?string $param): HTTPResponse
    {
        $app = ApplicationOperator::getApplication((int) $param, TRUE);

        $update = ApplicationUpdateDatabaseHandler::selectByApplication($app->getId(), 1)[0];

        return new HTTPResponse(HTTPResponse::OK, array(
            'status' => AttributeOperator::nameFromId($update->getStatus()),
            'time' => $update->getTime(),
            'user' => UserOperator::usernameFromId($update->getUser()),
            'description' => $update->getDescription()
        ));
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
     */
    private function createApplication(): HTTPResponse
    {
        $args = self::getFormattedBody(self::FIELDS);

        $errors = ApplicationOperator::createApplication($args);

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
    private function updateApplication(?string $param): HTTPResponse
    {
        $app = ApplicationOperator::getApplication((int) $param, TRUE);

        $args = self::getFormattedBody(self::FIELDS);

        $errors = ApplicationOperator::updateApplication($app, $args);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}