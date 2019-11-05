<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/04/2019
 * Time: 12:22 PM
 */


namespace extensions\facilities\controllers;


use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\ValidationError;
use extensions\facilities\business\FloorplanOperator;
use extensions\facilities\business\LocationOperator;
use extensions\facilities\business\SpaceOperator;
use models\HTTPRequest;
use models\HTTPResponse;

class SpaceController extends Controller
{
    public const NEW_SPACE_FIELDS = array('buildingCode', 'locationCode', 'floor', 'hexColor', 'unit', 'area');
    public const SPACE_FIELDS = array('hexColor', 'unit', 'area');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('facilitiescore_floorplans-r');

        $param = $this->request->next();

        $next = $this->request->next();

        if($this->request->method() === HTTPRequest::POST)
        {
            CurrentUserController::validatePermission('facilitiescore_floorplans-w');

            if($param === NULL)
                return $this->createSpace();
            else if($param !== NULL AND $next === 'points')
                return $this->addPoints((int)$param);
        }
        else if($this->request->method() === HTTPRequest::GET)
        {
            if($param === 'floor')
                return $this->getSpaceByFloor((int)$next);
            else if($param !== NULL)
                return $this->getSpace((int)$param);
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function createSpace(): HTTPResponse
    {
        $space = SpaceOperator::createSpace(self::getFormattedBody(self::NEW_SPACE_FIELDS));

        return new HTTPResponse(HTTPResponse::CREATED, array('location' => $space->getLocation()));
    }

    /**
     * @param int $location
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getSpace(int $location): HTTPResponse
    {
        $space = SpaceOperator::getSpace($location);
        $points = $space->getPoints();

        $pointArray = array();

        foreach($points as $point)
        {
            $pointArray[$point->getId()] = array('pD' => $point->getPD(), 'pR' => $point->getPR());
        }

        return new HTTPResponse(HTTPResponse::OK, array(
            'location' => $space->getLocation(),
            'floor' => $space->getFloor(),
            'hexCode' => $space->getHexColor(),
            'area' => $space->getArea(),
            'unit' => $space->getUnit(),
            'points' => $pointArray
        ));
    }

    /**
     * @param int $floor
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getSpaceByFloor(int $floor): HTTPResponse
    {
        $floor = FloorplanOperator::getFloorplan($floor);

        $data = array();

        foreach(SpaceOperator::getSpaceByFloor($floor) as $space)
        {
            $location = LocationOperator::getLocation($space->getLocation());

            $points = $space->getPoints();

            $pointArray = array();

            foreach($points as $point)
            {
                $pointArray[$point->getId()] = array('pD' => $point->getPD(), 'pR' => $point->getPR());
            }

            $data[] = array(
                'location' => $space->getLocation(),
                'code' => $location->getCode(),
                'name' => $location->getName(),
                'floor' => $space->getFloor(),
                'hexColor' => $space->getHexColor(),
                'area' => $space->getArea(),
                'unit' => $space->getUnit(),
                'points' => $pointArray);
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function addPoints(int $id)
    {
        $space = SpaceOperator::getSpace($id);

        $details = self::getFormattedBody(['points'], TRUE);

        if(!is_array($details['points']))
            throw new ValidationError(['Points not formatted correctly']);

        SpaceOperator::addPoints($space, $details['points']);
        return new HTTPResponse(HTTPResponse::CREATED);
    }
}