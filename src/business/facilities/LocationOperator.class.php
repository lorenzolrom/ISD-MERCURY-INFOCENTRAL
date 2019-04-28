<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/09/2019
 * Time: 11:26 PM
 */


namespace business\facilities;


use business\Operator;
use database\facilities\BuildingDatabaseHandler;
use database\facilities\LocationDatabaseHandler;
use exceptions\ValidationException;
use models\facilities\Building;
use models\facilities\Location;
use utilities\HistoryRecorder;

class LocationOperator extends Operator
{
    /**
     * @param int $id
     * @return Location
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getLocation(int $id): Location
    {
        return LocationDatabaseHandler::selectById($id);
    }

    /**
     * @param Building $building
     * @return Location[]
     * @throws \exceptions\DatabaseException
     */
    public static function getLocationsByBuilding(Building $building): array
    {
        return LocationDatabaseHandler::selectByBuilding($building->getId());
    }

    /**
     * @param Location $location
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryInUseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function deleteLocation(Location $location): bool
    {
        HistoryRecorder::writeHistory('FacilitiesCore_Location', HistoryRecorder::DELETE, $location->getId(), $location);

        LocationDatabaseHandler::delete($location->getId());

        return TRUE;
    }

    /**
     * @param Building $building
     * @param string|null $code
     * @param string|null $name
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function createLocation(Building $building, ?string $code, ?string $name): array
    {
        $errors = self::validateSubmission($building, $code, $name);

        if(!empty($errors))
            return array('errors' => $errors);

        $location = LocationDatabaseHandler::create($building->getId(), $code, $name);

        HistoryRecorder::writeHistory('FacilitiesCore_Location', HistoryRecorder::CREATE, $location->getId(), $location, array('building' => $building->getId(), 'code' => $code, 'name' => $name));

        return array('id' => $location->getId());

    }

    /**
     * @param Location $location
     * @param string|null $code
     * @param string|null $name
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function updateLocation(Location $location, ?string $code, ?string $name): array
    {
        $building = BuildingOperator::getBuilding($location->getBuilding());
        $errors = self::validateSubmission($building, $code, $name, $location);

        if(!empty($errors))
            return array('errors' => $errors);

        HistoryRecorder::writeHistory('FacilitiesCore_Location', HistoryRecorder::MODIFY, $location->getId(), $location, array('building' => $building->getId(), 'code' => $code, 'name' => $name));

        $newLocation = LocationDatabaseHandler::update($location->getId(), $code, $name);

        return array('id' => $newLocation->getId());
    }

    /**
     * @param Building $building
     * @param string $code
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function codeIsUnique(Building $building, string $code): bool
    {
        return !LocationDatabaseHandler::isCodeInUse($building->getId(), $code);
    }

    /**
     * @param int $id
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function getFullLocationCode(?int $id): ?string
    {
        if($id === NULL)
            return NULL;

        $fullLocationCode = BuildingDatabaseHandler::selectCodeFromId(LocationDatabaseHandler::selectBuildingFromId($id));

        $fullLocationCode .= " " . LocationDatabaseHandler::selectCodeFromId($id);

        return $fullLocationCode;
    }

    /**
     * @param int|null $id
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function codeFromId(?int $id): ?string
    {
        return LocationDatabaseHandler::selectCodeFromId((int) $id);
    }

    /**
     * @param Building $building
     * @param string|null $code
     * @param string|null $name
     * @param Location|null $location
     * @return array
     * @throws \exceptions\DatabaseException
     */
    private static function validateSubmission(Building $building, ?string $code, ?string $name, ?Location $location = NULL)
    {
        $errors = array();

        if($location === NULL OR $location->getCode() != $code)
        {
            try {Location::validateCode($building, $code);}
            catch (ValidationException $e){$errors[] = $e->getMessage();}
        }

        try{Location::validateName($name);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        return $errors;
    }
}