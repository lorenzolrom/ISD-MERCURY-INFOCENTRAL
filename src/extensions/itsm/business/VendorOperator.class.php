<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/25/2019
 * Time: 11:18 AM
 */


namespace extensions\itsm\business;


use business\Operator;
use extensions\itsm\database\PurchaseOrderDatabaseHandler;
use extensions\itsm\database\VendorDatabaseHandler;
use exceptions\EntryInUseException;
use exceptions\ValidationException;
use extensions\itsm\models\Vendor;
use utilities\HistoryRecorder;

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

        $vendor = VendorDatabaseHandler::insert($code, $name, $streetAddress, $city, $state, $zipCode, $phone, $fax);

        HistoryRecorder::writeHistory('ITSM_Vendor', HistoryRecorder::CREATE, $vendor->getId(), $vendor,
            array('code' => $code, 'name' => $name, 'streetAddress' => $streetAddress, 'city' => $city,
                'state' => $state, 'zipCode' => $zipCode, 'phone' => $phone, 'fax' => $fax));

        return array('id' => $vendor->getId());
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

        HistoryRecorder::writeHistory('ITSM_Vendor', HistoryRecorder::MODIFY, $vendor->getId(), $vendor,
            array('code' => $code, 'name' => $name, 'streetAddress' => $streetAddress, 'city' => $city,
                'state' => $state, 'zipCode' => $zipCode, 'phone' => $phone, 'fax' => $fax));

        $newVendor = VendorDatabaseHandler::update($vendor->getId(), $code, $name, $streetAddress, $city, $state, $zipCode, $phone, $fax);

        return array('id' => $newVendor->getId());
    }

    /**
     * @param Vendor $vendor
     * @return bool
     * @throws EntryInUseException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function deleteVendor(Vendor $vendor): bool
    {
        // Check if P.O. is referenced
        if(PurchaseOrderDatabaseHandler::doPurchaseOrdersReferenceVendor($vendor->getId()))
        {
            throw new EntryInUseException(EntryInUseException::MESSAGES[EntryInUseException::ENTRY_IN_USE], EntryInUseException::ENTRY_IN_USE);
        }

        HistoryRecorder::writeHistory('ITSM_Vendor', HistoryRecorder::DELETE, $vendor->getId(), $vendor);

        return VendorDatabaseHandler::delete($vendor->getId());
    }

    /**
     * @param string $code
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function codeInUse(string $code): bool
    {
        return VendorDatabaseHandler::selectIdFromCode($code) !== NULL;
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
    private static function validateSubmission(?string $code, ?string $name, ?string $streetAddress, ?string $city,
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
