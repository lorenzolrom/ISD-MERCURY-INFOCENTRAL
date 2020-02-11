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

        'bulletin' => 'Bulletin',
        'role' => 'Role',
        'secret' => 'Secret',
        'user' => 'User',
    );

    // Associate table with permissions
    private const TABLE_PERMISSIONS = array(
        'Bulletin' => 'settings',
        'Role' => 'settings',
        'Secret' => 'api-settings',
        'User' => 'settings',
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
        $objects = self::OBJECTS;
        $tablePermissions = self::TABLE_PERMISSIONS;

        // Import history from extensions
        foreach(\Config::OPTIONS['enabledExtensions'] as $extension)
        {
            $extConfigName = "extensions\\$extension\\ExtConfig"; // Build name of extension's ExtConfig

            // Skip if ExtConfig is not defined
            if(!class_exists($extConfigName))
                continue;

            // Merge HISTORY_OBJECTS and HISTORY_PERMISSIONS into OBJECTS and TABLE_PERMISSIONS
            $extConfig = new $extConfigName();

            if(defined("$extConfigName::HISTORY_OBJECTS"))
                $objects = array_merge($objects, $extConfig::HISTORY_OBJECTS);

            if(defined("$extConfigName::HISTORY_PERMISSIONS"))
                $tablePermissions = array_merge($tablePermissions, $extConfig::HISTORY_PERMISSIONS);
        }

        // Error if table is not defined in the permissions array
        if(!isset($objects[$objectName]))
            throw new EntryNotFoundException('Object type not found', EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        $tableName = $objects[$objectName];

        if(!isset($tablePermissions[$tableName]))
            throw new SecurityException('Object type does not have security filter', SecurityException::USER_NO_PERMISSION);

        CurrentUserController::validatePermission($tablePermissions[$tableName]);

        /**
         * Special scenarios
         */
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