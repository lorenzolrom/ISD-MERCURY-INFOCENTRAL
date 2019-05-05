<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/27/2019
 * Time: 10:30 PM
 */


namespace business;


use business\itsm\ApplicationOperator;
use business\itsm\AssetOperator;
use controllers\CurrentUserController;
use database\HistoryDatabaseHandler;
use exceptions\SecurityException;
use models\History;

class HistoryOperator extends Operator
{
    private const TABLE_PERMISSIONS = array(
        'FacilitiesCore_Building' => 'facilitiescore_facilities-r',
        'FacilitiesCore_Location' => 'facilitiescore_facilities-r',
        'ITSM_Asset' => 'itsm_inventory-assets-r',
        'ITSM_Commodity' => 'itsm_inventory-commodities-r',
        'ITSM_Host' => 'itsm_devices-hosts-r',
        'ITSM_Vendor' => 'itsm_inventory-vendors-r',
        'ITSM_Warehouse' => 'itsm_inventory-warehouses-r',
        'ITSM_Registrar' => 'itsm_web-registrars-r',
        'ITSM_VHost' => 'itsm_web-vhosts-r',
        'NIS_URLAlias' => 'itsm_web-aliases-rw',
        'ITSM_Application' => 'itsm_ait-apps-r',
        'Bulletin' => 'settings',
        'Role' => 'settings'
    );

    /**
     * @param string $tableName
     * @param string $index
     * @param string $action
     * @param string $username
     * @return History[]
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     */
    public static function getHistory(string $tableName, string $index, string $action = '%', string $username = '%'): array
    {
        // Error if table is not defined in the permissions array
        if(!isset(self::TABLE_PERMISSIONS[$tableName]))
            throw new SecurityException('Object type does not have security filter', SecurityException::USER_NO_PERMISSION);

        CurrentUserController::validatePermission(self::TABLE_PERMISSIONS[$tableName]);

        // Assets use their asset tags as 'primary' keys
        if($tableName == 'ITSM_Asset')
            $index = (string)AssetOperator::idFromAssetTag($index);
        if($tableName == 'ITSM_Application')
            $index = (string)ApplicationOperator::idFromNumber($index);

        return HistoryDatabaseHandler::select($tableName, $index, $action, $username);
    }
}