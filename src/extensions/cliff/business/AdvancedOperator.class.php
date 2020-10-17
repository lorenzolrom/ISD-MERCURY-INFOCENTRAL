<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/13/2020
 * Time: 11:48 AM
 */


namespace extensions\cliff\business;


use business\Operator;
use extensions\cliff\database\CoreDatabaseHandler;
use extensions\cliff\database\KeyDatabaseHandler;
use extensions\cliff\models\Core;
use extensions\cliff\models\Key;

/**
 * Handle advanced query operations
 *
 * Class AdvancedOperator
 * @package extensions\cliff\business
 */
class AdvancedOperator extends Operator
{
    /**
     * @param array $vals
     * @return Key[]
     * @throws \exceptions\DatabaseException
     */
    public static function xRefPeople(array $vals): array
    {
        return KeyDatabaseHandler::selectByIssuedTo((string)$vals['issuedTo']);
    }

    /**
     * @param array $vals
     * @return Core[]
     * @throws \exceptions\DatabaseException
     */
    public static function xRefLocations(array $vals): array
    {
        return CoreDatabaseHandler::selectByLocation((string)$vals['building'], (string)$vals['location']);
    }
}
