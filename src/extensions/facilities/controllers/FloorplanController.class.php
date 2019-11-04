<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/03/2019
 * Time: 10:54 AM
 */


namespace extensions\facilities\controllers;


use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\ValidationError;
use extensions\facilities\business\BuildingOperator;
use extensions\facilities\business\FloorplanOperator;
use models\HTTPRequest;
use models\HTTPResponse;

class FloorplanController extends Controller
{
    public const FLOORPLAN_IMAGE_NAME = 'floorplanImage';
    private const FLOORPLAN_FIELDS = array('buildingCode', 'floor');

    /**
     * @return HTTPResponse|null
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\UploadException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('facilitiescore_floorplans-r');
        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::POST)
        {
            if($param === NULL)
            {
                CurrentUserController::validatePermission('facilitiescore_floorplans-w');
                return $this->createFloorplan();
            }
            else if($param === 'search')
                return $this->search();
        }
        else if($this->request->method() === HTTPRequest::GET)
        {
            if($param !== NULL)
            {
                if($this->request->next() === 'image')
                    return $this->getFloorplanImage((int)$param);
                return $this->getFloorplanInfo((int)$param);
            }
            else
                return $this->search();
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\UploadException
     */
    private function createFloorplan(): HTTPResponse
    {
        // Get form fields
        $attrs = self::getFormattedBody(self::FLOORPLAN_FIELDS, TRUE);

        // IS is sending this as a normal POST request, the above won't get the values
        if(isset($_POST['buildingCode']))
            $attrs['buildingCode'] = $_POST['buildingCode'];
        if(isset($_POST['floor']))
            $attrs['floor'] = $_POST['floor'];

        // Get image
        if(empty($_FILES[self::FLOORPLAN_IMAGE_NAME]))
        {
            throw new ValidationError(array('Floorplan image required'));
        }

        $attrs[self::FLOORPLAN_IMAGE_NAME] = $_FILES[self::FLOORPLAN_IMAGE_NAME];

        $new = FloorplanOperator::createFloorplan($attrs);
        return new HTTPResponse(HTTPResponse::CREATED, array('id' => $new->getId()));
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function search(): HTTPResponse
    {
        $criteria = self::getFormattedBody(self::FLOORPLAN_FIELDS, FALSE);
        $floors = array();

        foreach(FloorplanOperator::getFloorplans($criteria['buildingCode'], $criteria['floor']) as $floorplan)
        {
            $building = BuildingOperator::getBuilding($floorplan->getBuilding());

            $floors[] = array(
                'id' => $floorplan->getId(),
                'buildingCode' => $building->getCode(),
                'buildingName' => $building->getName(),
                'floor' => $floorplan->getFloor()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $floors);
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getFloorplanInfo(int $id): HTTPResponse
    {
        $floorplan = FloorplanOperator::getFloorplan($id);
        $building = BuildingOperator::getBuilding($floorplan->getBuilding());

        return new HTTPResponse(HTTPResponse::OK, array(
            'id' => $floorplan->getId(),
            'floor' => $floorplan->getFloor(),
            'building' => $floorplan->getBuilding(),
            'buildingCode' => $building->getCode(),
            'buildingName' => $building->getName()
        ));
    }

    /**
     * @param int $id
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getFloorplanImage(int $id): void
    {
        $data = FloorplanOperator::getFloorplanImage($id);

        header('Content-Type: ' . $data['imageType']);
        echo file_get_contents($data['imagePath']);
        exit;
    }
}