<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 4/09/2019
 * Time: 9:07 PM
 */


namespace extensions\facilities\business;


use business\Operator;
use extensions\facilities\database\BuildingDatabaseHandler;
use extensions\facilities\database\LocationDatabaseHandler;
use exceptions\ValidationException;
use extensions\facilities\models\Building;
use utilities\HistoryRecorder;

class BuildingOperator extends Operator
{
    /**
     * @param int $id
     * @return Building
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getBuilding(int $id): Building
    {
        return BuildingDatabaseHandler::selectById($id);
    }

    /**
     * @param string|null $code
     * @param string|null $name
     * @param string|null $streetAddress
     * @param string|null $city
     * @param string|null $state
     * @param string|null $zipCode
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function createBuilding(?string $code, ?string $name, ?string $streetAddress, ?string $city, ?string $state, ?string $zipCode): array
    {
        $errors = self::validateSubmission($code, $name, $streetAddress, $city, $state, $zipCode);

        if(!empty($errors))
            return array('errors' => $errors);

        $building = BuildingDatabaseHandler::create($code, $name, $streetAddress, $city, $state, $zipCode);

        HistoryRecorder::writeHistory('FacilitiesCore_Building', HistoryRecorder::CREATE, $building->getId(), $building,
            array('code' => $code, 'name' => $name, 'streetAddress' => $streetAddress, 'city' => $city,
                'state' => $state, 'zipCode' => $zipCode));

        return array('id' => $building->getId());

    }

    /**
     * @param Building $building
     * @param string $code
     * @param string $name
     * @param string $streetAddress
     * @param string $city
     * @param string $state
     * @param string $zipCode
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function updateBuilding(Building $building, ?string $code, ?string $name, ?string $streetAddress, ?string $city, ?string $state, ?string $zipCode): array
    {
        $errors = self::validateSubmission($code, $name, $streetAddress, $city, $state, $zipCode, $building);

        if(!empty($errors))
            return array('errors' => $errors);

        HistoryRecorder::writeHistory('FacilitiesCore_Building', HistoryRecorder::MODIFY, $building->getId(), $building,
            array('code' => $code, 'name' => $name, 'streetAddress' => $streetAddress, 'city' => $city,
                'state' => $state, 'zipCode' => $zipCode));

        $newBuilding = BuildingDatabaseHandler::update($building->getId(), $code, $name, $streetAddress, $city, $state, $zipCode);

        return array('id' => $newBuilding->getId());
    }

    /**
     * @param Building $building
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryInUseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function deleteBuilding(Building $building): bool
    {
        foreach(LocationOperator::getLocationsByBuilding($building) as $location)
        {
            LocationOperator::deleteLocation($location);
        }

        HistoryRecorder::writeHistory('FacilitiesCore_Building', HistoryRecorder::DELETE, $building->getId(), $building);

        BuildingDatabaseHandler::delete($building->getId());

        return TRUE;
    }

    /**
     * @param string $code
     * @param string $name
     * @param string $streetAddress
     * @param string $city
     * @param string $state
     * @param string $zipCode
     * @return Building[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(string $code = '%', string $name ='%', string $streetAddress = '%', string $city = '%', string $state = '%', string $zipCode = '%'): array
    {
        return BuildingDatabaseHandler::select($code, $name, $streetAddress, $city, $state, $zipCode);
    }

    /**
     * @param string $code
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function codeIsUnique(string $code): bool
    {
        return !BuildingDatabaseHandler::codeInUse($code);
    }

    /**
     * @param int|null $id
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function codeFromId(?int $id): ?string
    {
        return BuildingDatabaseHandler::selectCodeFromId((int) $id);
    }

    /**
     * @param string|null $code
     * @param string|null $name
     * @param string|null $streetAddress
     * @param string|null $city
     * @param string|null $state
     * @param string|null $zipCode
     * @param Building|null $building
     * @return array
     * @throws \exceptions\DatabaseException
     */
    private static function validateSubmission(?string $code, ?string $name, ?string $streetAddress, ?string $city, ?string $state, ?string $zipCode, ?Building $building = NULL):array
    {
        $errors = array();

        // Validation
        if($building === NULL OR $building->getCode() != $code) // Skip if code is unchanged
        {
            try {Building::validateCode($code);}
            catch (ValidationException $e){$errors[] = $e->getMessage();}
        }

        try{Building::validateName($name);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        try{Building::validateStreetAddress($streetAddress);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        try{Building::validateCity($city);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        try{Building::validateState($state);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        try{Building::validateZipCode($zipCode);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        return $errors;
    }
}
