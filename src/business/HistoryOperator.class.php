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
        $objects = self::getMergedHistoryObjects();
        $tablePermissions = self::getMergedHistoryPermissions();

        // Error if table is not defined in the permissions array
        if(!isset($objects[$objectName]))
            throw new EntryNotFoundException('Object type not found', EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        $tableName = $objects[$objectName];

        if(!isset($tablePermissions[$tableName]))
            throw new SecurityException('Object type does not have security filter', SecurityException::USER_NO_PERMISSION);

        CurrentUserController::validatePermission($tablePermissions[$tableName]);

        // Check if table requires an ExtHistoryOperator
        $customScenarios = self::getTablesWithCustomHistoryOperators();
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
        $objects = self::getMergedHistoryObjects();
        $requiredPermissions = self::getMergedHistoryPermissions();

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
     * Get a merged set of history objects from all extensions
     * @return array|string[]
     */
    private static function getMergedHistoryObjects(): array
    {
        $objects = self::OBJECTS;

        foreach(Config::OPTIONS['enabledExtensions'] as $extension)
        {
            $extConfigName = "extensions\\$extension\\ExtConfig"; // Build name of extension's ExtConfig

            // Skip if ExtConfig is not defined
            if(!class_exists($extConfigName))
                continue;

            // Merge HISTORY_OBJECTS into OBJECTS
            $extConfig = new $extConfigName();

            if(defined("$extConfigName::HISTORY_OBJECTS"))
                $objects = array_merge($objects, $extConfig::HISTORY_OBJECTS);
        }

        return $objects;
    }

    /**
     * Get a merged set of permissions for history objects from all extensions
     * @return array|string[]
     */
    private static function getMergedHistoryPermissions(): array
    {
        $tablePermissions = self::TABLE_PERMISSIONS;

        // Import objects and permissions from extensions
        foreach(Config::OPTIONS['enabledExtensions'] as $extension)
        {
            $extConfigName = "extensions\\$extension\\ExtConfig"; // Build name of extension's ExtConfig

            // Skip if ExtConfig is not defined
            if(!class_exists($extConfigName))
                continue;

            // Merge HISTORY_PERMISSIONS into TABLE_PERMISSIONS
            $extConfig = new $extConfigName();

            if(defined("$extConfigName::HISTORY_PERMISSIONS"))
                $tablePermissions = array_merge($tablePermissions, $extConfig::HISTORY_PERMISSIONS);
        }

        return $tablePermissions;
    }

    /**
     * Get an array of table names that require an extensions custom ExtHistoryOperator
     * @return array with table names as the keys, and instances of the appropriate ExtHistoryOperators as values
     */
    private static function getTablesWithCustomHistoryOperators(): array
    {
        $customOperators = array();

        foreach(Config::OPTIONS['enabledExtensions'] as $extension)
        {
            $extConfigName = "extensions\\$extension\\ExtConfig"; // Build name of extension's ExtConfig

            // Skip if ExtConfig is not defined
            if(!class_exists($extConfigName))
                continue;

            $extConfig = new $extConfigName();

            if(defined("$extConfigName::HISTORY_CUSTOM_OPERATOR") AND is_array($extConfig::HISTORY_CUSTOM_OPERATOR))
            {
                $extHistoryOperatorName = "extensions\\$extension\utilities\ExtHistoryOperator";
                if(class_exists($extHistoryOperatorName))
                {
                    $extHistoryOperator = new $extHistoryOperatorName;
                    foreach($extConfig::HISTORY_CUSTOM_OPERATOR as $tableName)
                    {
                        $customOperators[$tableName] = $extHistoryOperator;
                    }
                }
            }
        }

        return $customOperators;
    }
}
