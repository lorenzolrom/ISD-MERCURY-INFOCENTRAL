<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/27/2019
 * Time: 10:30 PM
 */


namespace business;


use Config;
use exceptions\DatabaseException;
use controllers\CurrentUserController;
use database\HistoryDatabaseHandler;
use exceptions\EntryNotFoundException;
use exceptions\SecurityException;
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
     * @throws DatabaseException
     * @throws EntryNotFoundException
     */
    public static function getHistory(string $objectName, string $index = "%", string $action = '%', string $username = '%'): array
    {
        $mergedOptions = self::getMergedOptions(true, true, true);
        $objects = $mergedOptions['objects'];
        $tablePermissions = $mergedOptions['permissions'];
        $customScenarios = $mergedOptions['customOperators'];

        // Error if table is not defined in the permissions array
        if(!isset($objects[$objectName]))
            throw new EntryNotFoundException('Object type not found', EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        $tableName = $objects[$objectName];

        if(!isset($tablePermissions[$tableName]))
            throw new SecurityException('Object type does not have security filter', SecurityException::USER_NO_PERMISSION);

        CurrentUserController::validatePermission($tablePermissions[$tableName]);

        // Check if table requires an ExtHistoryOperator
        if(in_array($tableName, array_keys($customScenarios)))
        {
            // Get the result of the extension's custom ExtHistoryOperator
            return $customScenarios[$tableName]::getHistory($tableName, $index, $action, $username);
        }

        return HistoryDatabaseHandler::select($tableName, $index, $action, $username);
    }

    /**
     * Return a list of history objects that the current user has permission for
     * @return array
     * @throws DatabaseException
     * @throws SecurityException
     */
    public static function getHistoryObjects(): array
    {
        $mergedOptions = self::getMergedOptions(true, true);
        $objects = $mergedOptions['objects'];
        $requiredPermissions = $mergedOptions['permissions'];

        // Array of current user's permissions
        $currentUserPermissions = CurrentUserController::currentUser()->getPermissions();

        // Objects that the user has permission to view
        $validObjects = array();

        foreach(array_keys($objects) as $object)
        {
            $permission = $requiredPermissions[$objects[$object]]; // Get the permission code associated with the table of this object

            // Does the current user have permission to do this?
            if(!in_array($permission, $currentUserPermissions))
                continue;

            $validObjects[] = $object;
        }

        return $validObjects;
    }

    /**
     * @param bool $includeObjects Include [operators]?
     * @param bool $includePermissions Include [permissions]?
     * @param bool $includeCustomOperators Include [customOperators]?
     * @return array
     */
    private static function getMergedOptions(bool $includeObjects = false, bool $includePermissions = false, bool $includeCustomOperators = false): array
    {
        $mergedOptions = array();

        if($includeObjects)
            $mergedOptions['objects'] = array();
        if($includePermissions)
            $mergedOptions['permissions'] = array();
        if($includeCustomOperators)
            $mergedOptions['customOperators'] = array();

        foreach(Config::OPTIONS['enabledExtensions'] as $extensionName)
        {
            // Build name of extension's ExtConfig class
            $extConfigName = "extensions\\$extensionName\\ExtConfig";

            // Skip if ExtConfig is not defined
            if(!class_exists($extConfigName))
                continue;

            // Instantiate new ExtConfig
            $extConfig = new $extConfigName;

            // History Object Types
            if($includeObjects)
            {
                if(defined("$extConfigName::HISTORY_OBJECTS"))
                    $mergedOptions['objects'] = array_merge($mergedOptions['objects'], $extConfig::HISTORY_OBJECTS);
            }

            // History Table Permissions
            if($includePermissions)
            {
                if(defined("$extConfigName::HISTORY_PERMISSIONS"))
                    $mergedOptions['permissions'] = array_merge($mergedOptions['permissions'], $extConfig::HISTORY_PERMISSIONS);
            }

            // Custom History Scenarios
            if($includeCustomOperators)
            {
                if(defined("$extConfigName::HISTORY_CUSTOM_OPERATOR") AND is_array($extConfig::HISTORY_CUSTOM_OPERATOR))
                {
                    $extHistoryOperatorName = "extensions\\$extensionName\utilities\ExtHistoryOperator";
                    if(class_exists($extHistoryOperatorName))
                    {
                        $extHistoryOperator = new $extHistoryOperatorName;
                        foreach($extConfig::HISTORY_CUSTOM_OPERATOR as $tableName)
                        {
                            $mergedOptions['customOperators'][$tableName] = $extHistoryOperator;
                        }
                    }
                }
            }
        }

        return $mergedOptions;
    }
}
