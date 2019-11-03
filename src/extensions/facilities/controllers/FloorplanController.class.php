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
        return new HTTPResponse(HTTPResponse::CREATED, array('building' => $new->getBuilding(), 'floor' => $new->getFloor()));
    }
}