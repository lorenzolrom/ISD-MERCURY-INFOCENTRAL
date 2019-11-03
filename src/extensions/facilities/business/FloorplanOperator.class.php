<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/03/2019
 * Time: 10:46 AM
 */


namespace extensions\facilities\business;


use business\Operator;
use exceptions\EntryNotFoundException;
use exceptions\UploadException;
use exceptions\ValidationError;
use exceptions\ValidationException;
use extensions\facilities\controllers\FloorplanController;
use extensions\facilities\database\BuildingDatabaseHandler;
use extensions\facilities\database\FloorplanDatabaseHandler;
use extensions\facilities\models\Floorplan;
use utilities\HistoryRecorder;

class FloorplanOperator extends Operator
{
    // Path for uploaded images relative to 'src'
    public const IMAGE_PATH = 'uploaded/';

    /**
     * @param array $attrs
     * @return Floorplan
     * @throws EntryNotFoundException
     * @throws UploadException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function createFloorplan(array $attrs): Floorplan
    {
        $errors = array();

        // Validate building code
        $building = BuildingDatabaseHandler::selectIdFromCode((string)$attrs['buildingCode']);

        if($building === NULL)
            $errors[] = 'Building not found';

        if(!empty($errors))
            throw new ValidationError($errors);

        // Validate floor name
        try
        {
            Floorplan::validateFloor((string)$attrs['floor']);
        }
        catch(ValidationException $e)
        {
            $errors[] = $e->getMessage();
        }

        // Does floor already exist
        try
        {
            FloorplanDatabaseHandler::select($building, (string)$attrs['floor']);
            $errors[] = 'Floor already exists';
        }
        catch(EntryNotFoundException $e){} // Do nothing

        // Validate image
        $image = $attrs[FloorplanController::FLOORPLAN_IMAGE_NAME];

        try
        {
            Floorplan::validateImageType($image['type']);
        }
        catch(ValidationException $e)
        {
            $errors .= 'Image type must be: ' . implode(', ', Floorplan::IMAGE_TYPE_RULES['acceptable']);
        }

        if(!empty($errors))
            throw new ValidationError($errors);

        // Process file upload
        $fileName = $building . '_' . $attrs['floor'] . '.flr';
        $filePath = dirname(__FILE__) . '/../' . self::IMAGE_PATH .  $fileName;


        if(!move_uploaded_file($image['tmp_name'], $filePath))
            throw new UploadException(UploadException::MESSAGES[UploadException::MOVE_UPLOADED_FILE_FAILED], UploadException::MOVE_UPLOADED_FILE_FAILED);

        // Add to database
        $newFloor = FloorplanDatabaseHandler::insert($building, $attrs['floor'], $image['type'], $fileName);
        HistoryRecorder::writeHistory('Facilities_Floorplan', HistoryRecorder::CREATE, $newFloor->getId(), $newFloor);

        return $newFloor;
    }

    public static function updateFloorplan()
    {

    }

    public static function deleteFloorplan()
    {

    }

    public static function getFloorplans(string $buildingCode)
    {

    }
}