<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/09/2019
 * Time: 8:51 PM
 */


namespace controllers\facilities;


use business\facilities\BuildingOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class BuildingController extends Controller
{
    private const SEARCH_FIELDS = array('code', 'name', 'streetAddress', 'city', 'state', 'zipCode');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\EntryInUseException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission(array('facilitiescore_facilities-r', 'itsm_inventory-assets-w'));

        $param = $this->request->next();

        if($this->request->method() == HTTPRequest::GET)
        {
            switch($param)
            {
                case null:
                    return $this->getSearchResult();
                default:
                    switch($this->request->next())
                    {
                        case "locations":
                            return $this->getBuildingLocations($param);
                    }
                    return $this->getBuilding($param);
            }
        }
        else if($this->request->method() == HTTPRequest::PUT)
        {
            return $this->updateBuilding($param);
        }
        else if($this->request->method() == HTTPRequest::POST)
        {
            switch($param)
            {
                case "search":
                    return $this->getSearchResult(TRUE);
                default:
                    return $this->createBuilding();
            }
        }
        else if($this->request->method() == HTTPRequest::DELETE)
        {
            return $this->deleteBuilding($param);
        }

        return NULL;
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getBuilding(?string $param): HTTPResponse
    {
        $building = BuildingOperator::getBuilding((int)$param);

        $data = array(
            'id' => $building->getId(),
            'code' => $building->getCode(),
            'name' => $building->getName(),
            'streetAddress' => $building->getStreetAddress(),
            'city' => $building->getCity(),
            'state' => $building->getState(),
            'zipCode' => $building->getZipCode()
        );

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getBuildingLocations(?string $param): HTTPResponse
    {
        $building = BuildingOperator::getBuilding((int)$param);

        $locations = array();

        foreach($building->getLocations() as $location)
        {
            $locations[] = array(
                'id' => $location->getId(),
                'code' => $location->getCode(),
                'name' => $location->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $locations);
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

            $buildings = BuildingOperator::search($args['code'], $args['name'], $args['streetAddress'], $args['city'], $args['state'], $args['zipCode']);
        }
        else
        {
            $buildings = BuildingOperator::search();
        }

        $data = array();

        foreach($buildings as $building)
        {
            $data[] = array(
                'id' => $building->getId(),
                'code' => $building->getCode(),
                'name' => $building->getName(),
                'streetAddress' => $building->getStreetAddress(),
                'city' => $building->getCity(),
                'state' => $building->getState(),
                'zipCode' => $building->getZipCode()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function updateBuilding(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('facilitiescore_facilities-w'));

        $building = BuildingOperator::getBuilding((int)$param);

        $fields = $this->getFormattedBody(self::SEARCH_FIELDS, TRUE);

        $errors = BuildingOperator::updateBuilding($building, $fields['code'], $fields['name'], $fields['streetAddress'], $fields['city'], $fields['state'], $fields['zipCode']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\EntryInUseException
     */
    private function deleteBuilding(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('facilitiescore_facilities-w'));

        BuildingOperator::deleteBuilding(BuildingOperator::getBuilding((int) $param));

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function createBuilding(): HTTPResponse
    {
        CurrentUserController::validatePermission(array('facilitiescore_facilities-w'));

        $fields = $this->getFormattedBody(self::SEARCH_FIELDS, TRUE);

        $errors = BuildingOperator::createBuilding($fields['code'], $fields['name'], $fields['streetAddress'], $fields['city'], $fields['state'], $fields['zipCode']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::CREATED, $errors);
    }
}