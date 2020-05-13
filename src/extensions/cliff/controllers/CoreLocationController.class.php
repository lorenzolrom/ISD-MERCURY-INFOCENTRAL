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
use extensions\cliff\business\CoreOperator;
use extensions\cliff\models\Core;
use models\HTTPRequest;
use models\HTTPResponse;

class CoreLocationController extends Controller
{
    private const LOCATION_FIELDS = array('building', 'location', 'notes');

    private $core;

    public function __construct(Core $core, HTTPRequest $request)
    {
        $this->core = $core;
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
        $p1 = $this->request->next(); // Optional ID of location resource

        if($this->request->method() === HTTPRequest::GET)
        {
            if($p1 === NULL)
                return $this->getAllLocations();
            else
                return $this->getLocation((int)$p1);
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            if($p1 === NULL)
                return $this->createLocation();
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            if($p1 !== NULL)
                return $this->updateLocation((int)$p1);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            if($p1 !== NULL)
                return $this->deleteLocation((int)$p1);
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function getAllLocations(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-r');
        return new HTTPResponse(HTTPResponse::OK, CoreOperator::getLocations($this->core));
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function getLocation(int $id): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-r');
        return new HTTPResponse(HTTPResponse::OK, (array)CoreOperator::getLocation($id));
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function createLocation(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-w');
        return new HTTPResponse(HTTPResponse::CREATED, array('id' => CoreOperator::createLocation($this->core, self::getFormattedBody(self::LOCATION_FIELDS))->getId()));
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function updateLocation(int $id): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-w');
        $location = CoreOperator::getLocation($id);
        CoreOperator::updateLocation($location, self::getFormattedBody(self::LOCATION_FIELDS));
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function deleteLocation(int $id): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-w');
        $location = CoreOperator::getLocation($id);
        CoreOperator::deleteLocation($location);
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}