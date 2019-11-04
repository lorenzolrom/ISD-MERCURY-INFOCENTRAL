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
            FloorplanDatabaseHandler::selectByBuildingFloor($building, (string)$attrs['floor']);
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
            $errors[] = 'Image type must be: ' . implode(', ', Floorplan::IMAGE_TYPE_RULES['acceptable']);
        }

        if(!empty($errors))
            throw new ValidationError($errors);

        // Process file upload
        $fileName = $building . '_' . $attrs['floor'] . date_timestamp_get(date_create()) . '.flr';
        $filePath = dirname(__FILE__) . '/../' . self::IMAGE_PATH .  $fileName;


        if(!move_uploaded_file($image['tmp_name'], $filePath))
            throw new UploadException(UploadException::MESSAGES[UploadException::MOVE_UPLOADED_FILE_FAILED], UploadException::MOVE_UPLOADED_FILE_FAILED);

        // Add to database
        $newFloor = FloorplanDatabaseHandler::insert($building, $attrs['floor'], $image['type'], $fileName);
        HistoryRecorder::writeHistory('Facilities_Floorplan', HistoryRecorder::CREATE, $newFloor->getId(), $newFloor);

        return $newFloor;
    }

    /**
     * @param $id
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function getFloorplanImage($id): array
    {
        $floorplan = self::getFloorplan($id);

        $filePath = dirname(__FILE__) .'/../' . self::IMAGE_PATH . $floorplan->getImageName();

        return array(
            'imageType' => $floorplan->getImageType(),
            'imagePath' => $filePath
        );
    }

    /**
     * @param string $buildingCode
     * @param string $floor
     * @return Floorplan[]
     * @throws \exceptions\DatabaseException
     */
    public static function getFloorplans(string $buildingCode, string $floor): array
    {
        return FloorplanDatabaseHandler::select($buildingCode, $floor);
    }

    /**
     * @param int $id
     * @return Floorplan
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function getFloorplan(int $id): Floorplan
    {
        return FloorplanDatabaseHandler::selectById($id);
    }

    /**
     * @param Floorplan $floorplan
     * @param array $attrs
     * @return Floorplan
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function updateFloorplan(Floorplan $floorplan, array $attrs): Floorplan
    {
        $errors = array();

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
        if($floorplan->getFloor() !== $attrs['floor'])
        {
            try
            {
                FloorplanDatabaseHandler::selectByBuildingFloor($floorplan->getBuilding(), (string)$attrs['floor']);
                $errors[] = 'Floor already exists';
            }
            catch(EntryNotFoundException $e){} // Do nothing
        }

        if(!empty($errors))
            throw new ValidationError($errors);

        // Update database record
        HistoryRecorder::writeHistory('Facilities_Floorplan', HistoryRecorder::MODIFY, $floorplan->getId(), $floorplan, $attrs);
        return FloorplanDatabaseHandler::update($floorplan->getId(), $floorplan->getBuilding(), $floorplan->getFloor(), $attrs['floor'], $floorplan->getImageType(), $floorplan->getImageName());
    }

    /**
     * @param Floorplan $floorplan
     * @return bool
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function delete(Floorplan $floorplan): bool
    {
        HistoryRecorder::writeHistory('Facilities_Floorplan', HistoryRecorder::DELETE, $floorplan->getId(), $floorplan);

        // Remove file
        $filePath = dirname(__FILE__) . '/../' . self::IMAGE_PATH .  $floorplan->getImageName();
        unlink($filePath);

        // Remove from database
        FloorplanDatabaseHandler::delete($floorplan->getBuilding(), $floorplan->getFloor());

        return TRUE;
    }

    /**
     * @param Floorplan $fp
     * @param array $image
     * @return bool
     * @throws EntryNotFoundException
     * @throws UploadException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function updateFloorplanImage(Floorplan $fp, array $image): bool
    {
        $errors = array();

        if(!empty($errors))
            throw new ValidationError($errors);

        // Validate image
        try
        {
            Floorplan::validateImageType($image['type']);
        }
        catch(ValidationException $e)
        {
            $errors[] = 'Image type must be: ' . implode(', ', Floorplan::IMAGE_TYPE_RULES['acceptable']);
        }

        if(!empty($errors))
            throw new ValidationError($errors);

        // Process file upload - put in existing location
        $filePath = dirname(__FILE__) . '/../' . self::IMAGE_PATH .  $fp->getImageName();


        if(!move_uploaded_file($image['tmp_name'], $filePath))
            throw new UploadException(UploadException::MESSAGES[UploadException::MOVE_UPLOADED_FILE_FAILED], UploadException::MOVE_UPLOADED_FILE_FAILED);

        // Add to database
        $hist = HistoryRecorder::writeHistory('Facilities_Floorplan', HistoryRecorder::MODIFY, $fp->getId(), $fp);
        HistoryRecorder::writeAssocHistory($hist, array('systemEntry' => array('Updated Floorplan Image')));

        return TRUE;
    }
}