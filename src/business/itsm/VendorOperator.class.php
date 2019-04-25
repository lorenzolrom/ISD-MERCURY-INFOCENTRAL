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
use database\itsm\VendorDatabaseHandler;
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
}