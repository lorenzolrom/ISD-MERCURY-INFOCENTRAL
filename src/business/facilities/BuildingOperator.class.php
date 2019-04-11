<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/09/2019
 * Time: 9:07 PM
 */


namespace business\facilities;


use business\Operator;
use controllers\CurrentUserController;
use database\facilities\BuildingDatabaseHandler;
use database\facilities\LocationDatabaseHandler;
use exceptions\ValidationException;
use models\facilities\Building;

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
    public static function createBuilding(?string $code, ?string $name, ?string $streetAddress,?string $city, ?string $state, ?string $zipCode): array
    {
        $errors = self::validateSubmission($code, $name, $streetAddress, $city, $state, $zipCode);
        $user = CurrentUserController::currentUser();

        if(empty($errors))
            return array('id' => BuildingDatabaseHandler::create($code, $name, $streetAddress, $city, $state, $zipCode, date('Y-m-d'), $user->getId(), date('Y-m-d'), $user->getId())->getId());

        return array('errors' => $errors);
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

        if(empty($errors))
            BuildingDatabaseHandler::update($building->getId(), $code, $name, $streetAddress, $city, $state, $zipCode, date('Y-m-d'), CurrentUserController::currentUser()->getId());

        return $errors;
    }

    /**
     * @param Building $building
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryInUseException
     */
    public static function deleteBuilding(Building $building): bool
    {
        // Delete locations
        LocationDatabaseHandler::deleteByBuilding($building->getId());

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