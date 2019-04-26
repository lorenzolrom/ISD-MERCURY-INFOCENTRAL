<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/25/2019
 * Time: 11:18 AM
 */


namespace business\itsm;


use business\Operator;
use controllers\CurrentUserController;
use database\itsm\PurchaseOrderDatabaseHandler;
use database\itsm\ReturnOrderDatabaseHandler;
use database\itsm\VendorDatabaseHandler;
use exceptions\EntryInUseException;
use exceptions\ValidationException;
use models\itsm\Vendor;

class VendorOperator extends Operator
{
    /**
     * @param int $id
     * @return Vendor
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getVendor(int $id): Vendor
    {
        return VendorDatabaseHandler::selectById($id);
    }

    /**
     * @param string $code
     * @param string $name
     * @param string $streetAddress
     * @param string $city
     * @param string $state
     * @param string $zipCode
     * @param string $phone
     * @param string $fax
     * @return Vendor[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(string $code = '%', string $name = '%', string $streetAddress = '%',
                                  string $city = '%', string $state = '%', string $zipCode = '%', string $phone = '%',
                                  string $fax = '%'): array
    {
        return VendorDatabaseHandler::select($code, $name, $streetAddress, $city, $state, $zipCode, $phone, $fax);
    }

    /**
     * @param string|null $code
     * @param string|null $name
     * @param string|null $streetAddress
     * @param string|null $city
     * @param string|null $state
     * @param string|null $zipCode
     * @param string|null $phone
     * @param string|null $fax
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function createVendor(?string $code, ?string $name, ?string $streetAddress, ?string $city,
                                        ?string $state, ?string $zipCode, ?string $phone, ?string $fax): array
    {
        $errors = self::validateSubmission($code, $name, $streetAddress, $city, $state, $zipCode, $phone, $fax);

        if(!empty($errors))
            return array('errors' => $errors);

        $user = CurrentUserController::currentUser();

        return array('id' => VendorDatabaseHandler::insert($code, $name, $streetAddress, $city, $state, $zipCode, $phone, $fax, date('Y-m-d'), $user->getId())->getId());
    }

    /**
     * @param Vendor $vendor
     * @param string|null $code
     * @param string|null $name
     * @param string|null $streetAddress
     * @param string|null $city
     * @param string|null $state
     * @param string|null $zipCode
     * @param string|null $phone
     * @param string|null $fax
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function updateVendor(Vendor $vendor, ?string $code, ?string $name, ?string $streetAddress, ?string $city,
                                        ?string $state, ?string $zipCode, ?string $phone, ?string $fax): array
    {
        $errors = self::validateSubmission($code, $name, $streetAddress, $city, $state, $zipCode, $phone, $fax, $vendor);

        if(!empty($errors))
            return array('errors' => $errors);

        $user = CurrentUserController::currentUser();

        return array('id' => VendorDatabaseHandler::update($vendor->getId(), $code, $name, $streetAddress, $city, $state, $zipCode, $phone, $fax, date('Y-m-d'), $user->getId())->getId());
    }

    /**
     * @param Vendor $vendor
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws EntryInUseException
     */
    public static function deleteVendor(Vendor $vendor): bool
    {
        // Check if P.O. is referenced
        if(PurchaseOrderDatabaseHandler::doPurchaseOrdersReferenceVendor($vendor->getId())
            OR ReturnOrderDatabaseHandler::doReturnOrdersReferenceVendor($vendor->getId()))
        {
            throw new EntryInUseException(EntryInUseException::MESSAGES[EntryInUseException::ENTRY_IN_USE], EntryInUseException::ENTRY_IN_USE);
        }

        return VendorDatabaseHandler::delete($vendor->getId());
    }

    /**
     * @param string|null $code
     * @param string|null $name
     * @param string|null $streetAddress
     * @param string|null $city
     * @param string|null $state
     * @param string|null $zipCode
     * @param string|null $phone
     * @param string|null $fax
     * @param Vendor|null $vendor
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function validateSubmission(?string $code, ?string $name, ?string $streetAddress, ?string $city,
                                              ?string $state, ?string $zipCode, ?string $phone, ?string $fax,
                                              ?Vendor $vendor = NULL): array
    {
        $errors = array();

        // Code
        if($vendor === NULL OR $vendor->getCode() != $code)
        {
            try{Vendor::validateCode($code);}
            catch(ValidationException $e){$errors[] = $e->getMessage();}
        }

        // Name
        try{Vendor::validateName($name);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // Street Address
        try{Vendor::validateStreetAddress($streetAddress);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // City
        try{Vendor::validateCity($city);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // State
        try{Vendor::validateState($state);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // ZipCode
        try{Vendor::validateZipCode($zipCode);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // Phone
        try{Vendor::validatePhone($phone);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // Fax
        try{Vendor::validateFax($fax);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        return $errors;
    }
}