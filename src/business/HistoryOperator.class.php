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


use exceptions\LDAPException;
use extensions\itsm\business\ApplicationOperator;
use extensions\itsm\business\AssetOperator;
use extensions\itsm\business\DiscardOrderOperator;
use extensions\itsm\business\PurchaseOrderOperator;
use controllers\CurrentUserController;
use database\HistoryDatabaseHandler;
use exceptions\EntryNotFoundException;
use exceptions\SecurityException;
use extensions\netuserman\business\NetGroupOperator;
use extensions\netuserman\business\NetUserOperator;
use models\History;

class HistoryOperator extends Operator
{
    // Convert friendly name to table
    private const OBJECTS = array(
        'hostcategory' => 'ITSM_HostCategory',
        'building' => 'FacilitiesCore_Building',
        'location' => 'FacilitiesCore_Location',
        'floorplan' => 'Facilities_Floorplan',
        'asset' => 'ITSM_Asset',
        'commodity' => 'ITSM_Commodity',
        'host' => 'ITSM_Host',
        'vendor' => 'ITSM_Vendor',
        'warehouse' => 'ITSM_Warehouse',
        'registrar' => 'ITSM_Registrar',
        'vhost' => 'ITSM_VHost',
        'urlalias' => 'NIS_URLAlias',
        'application' => 'ITSM_Application',
        'bulletin' => 'Bulletin',
        'role' => 'Role',
        'secret' => 'Secret',
        'user' => 'User',
        'purchaseorder' => 'ITSM_PurchaseOrder',
        'discardorder' => 'ITSM_DiscardOrder',
        'workspace' => 'Tickets_Workspace',
        'team' => 'Tickets_Team',
        'ticket' => 'Tickets_Ticket',
        'netuser' => '!NETUSER',
        'netgroup' => '!NETGROUP'
    );

    // Associate table with permissions
    private const TABLE_PERMISSIONS = array(
        'Tickets_Workspace' => 'tickets-admin',
        'Tickets_Team' => 'tickets-admin',
        'Tickets_Ticket' => 'tickets-agent',
        'FacilitiesCore_Building' => 'facilitiescore_facilities-r',
        'FacilitiesCore_Location' => 'facilitiescore_facilities-r',
        'Facilities_Floorplan' => 'facilitiescore_floorplans-r',
        'ITSM_Asset' => 'itsm_inventory-assets-r',
        'ITSM_Commodity' => 'itsm_inventory-commodities-r',
        'ITSM_Host' => 'itsm_devices-hosts-r',
        'ITSM_Vendor' => 'itsm_inventory-vendors-r',
        'ITSM_Warehouse' => 'itsm_inventory-warehouses-r',
        'ITSM_Registrar' => 'itsm_web-registrars-r',
        'ITSM_VHost' => 'itsm_web-vhosts-r',
        'NIS_URLAlias' => 'itsm_web-aliases-rw',
        'ITSM_Application' => 'itsm_ait-apps-r',
        'ITSM_HostCategory' => 'itsmmonitor-hosts-w',
        'ITSM_PurchaseOrder' => 'itsm_inventory-purchaseorders-r',
        'ITSM_DiscardOrder' => 'itsm_inventory-discards-r',
        '!NETUSER' => 'netuserman-read',
        '!NETGROUP' => 'netuserman-readgroups',
        'Bulletin' => 'settings',
        'Role' => 'settings',
        'Secret' => 'api-settings',
        'User' => 'settings'
    );

    /**
     * @param string $objectName
     * @param string $index
     * @param string $action
     * @param string $username
     * @return History[]
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     */
    public static function getHistory(string $objectName, string $index, string $action = '%', string $username = '%'): array
    {
        // Error if table is not defined in the permissions array
        if(!isset(self::OBJECTS[$objectName]))
            throw new EntryNotFoundException('Object type not found', EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        $tableName = self::OBJECTS[$objectName];

        if(!isset(self::TABLE_PERMISSIONS[$tableName]))
            throw new SecurityException('Object type does not have security filter', SecurityException::USER_NO_PERMISSION);

        CurrentUserController::validatePermission(self::TABLE_PERMISSIONS[$tableName]);

        if($tableName == 'ITSM_Asset') // Assets use their asset tags as 'primary' keys
            $index = (string)AssetOperator::idFromAssetTag($index);
        else if($tableName == 'ITSM_Application') // Convert Number to ID
            $index = (string)ApplicationOperator::idFromNumber($index);
        else if($tableName == 'ITSM_PurchaseOrder') // Convert Number to ID
            $index = (string)PurchaseOrderOperator::idFromNumber($index);
        else if($tableName == 'ITSM_DiscardOrder') // Convert Number to ID
            $index = (string)DiscardOrderOperator::idFromNumber($index);
        else if($tableName == '!NETUSER') // Convert username to GUID
        {
            try
            {
                // Convert only the sam account name to guid
                $index = (string)NetUserOperator::getUserDetails($index, ['objectguid'])['objectguid'];
            }
            catch(LDAPException $e)
            {
                throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND, $e);
            }
        }
        else if($tableName == '!NETGROUP') // Convert group CN to GUID
        {
            try
            {
                $index = (string)NetGroupOperator::getGroupDetails($index, ['objectguid'])['objectguid'];
            }
            catch(LDAPException $e)
            {
                throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND, $e);
            }
        }
        return HistoryDatabaseHandler::select($tableName, $index, $action, $username);
    }
}