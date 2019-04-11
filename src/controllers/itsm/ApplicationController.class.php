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


use business\itsm\ApplicationOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class ApplicationController extends Controller
{
    const SEARCH_FIELDS = array('number', 'name', 'description', 'ownerUsername', 'type', 'publicFacing', 'lifeExpectancy', 'dataVolume', 'authType', 'port', 'host', 'vhost', 'status');

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
                case null:
                    return $this->getSearchResult();
                default:
                    return $this->getByNumber($param);
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
    private function getByNumber(?string $param): HTTPResponse
    {
        $application = ApplicationOperator::getApplication((int)$param, TRUE);

        $data = array(
            'number' => $application->getNumber(),
            'name' => $application->getName(),
            'description' => $application->getDescription(),
            'owner' => $application->getOwner(),
            'type' => $application->getType(),
            'status' => $application->getStatus(),
            'publicFacing' => $application->getPublicFacing(),
            'lifeExpectancy' => $application->getLifeExpectancy(),
            'dataVolume' => $application->getDataVolume(),
            'authType' => $application->getAuthType(),
            'port' => $application->getPort(),
            'createUser' => $application->getCreateUser(),
            'createDate' => $application->getCreateDate(),
            'lastModifyUser' => $application->getLastModifyUser(),
            'lastModifyDate' => $application->getLastModifyDate()
        );

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param bool $search
     * @param bool $strict
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getSearchResult(bool $search = FALSE, bool $strict = FALSE): HTTPResponse
    {
        if($search)
        {
            $args = $this->getFormattedBody(self::SEARCH_FIELDS, $strict);

            $apps = ApplicationOperator::search($args['number'], $args['name'], $args['description'], $args['ownerUsername'], $args['type'], $args['publicFacing'],
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
                'type' => $app->getType(),
                'status' => $app->getStatus(),
                'owner' => $app->getOwner()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $results);
    }
}