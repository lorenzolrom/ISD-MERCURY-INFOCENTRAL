<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/26/2019
 * Time: 12:02 PM
 */


namespace utilities;


use controllers\CurrentUserController;
use database\HistoryDatabaseHandler;
use models\Model;

class HistoryRecorder
{
    public const CREATE = 'CREATE';
    public const MODIFY = 'MODIFY';
    public const DELETE = 'DELETE';

    /**
     * @param string $tableName
     * @param string $action
     * @param string $index
     * @param Model $currentState
     * @param array $newValues
     * @return void
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function writeHistory(string $tableName, string $action, string $index, Model $currentState, array $newValues = array()): void
    {
        $rawOldValues = (array)$currentState;
        $oldValues = array();

        foreach(array_keys($rawOldValues) as $varName)
        {
            $parts =  explode(str_split($varName)[0], $varName);
            $shortVarName = $parts[sizeof($parts) - 1];

            $oldValues[$shortVarName] = $rawOldValues[$varName];
        }

        $record = HistoryDatabaseHandler::insert($tableName, $action, $index, CurrentUserController::currentUser()->getUsername(), date('Y-m-d H:i:s'));

        foreach(array_keys($newValues) as $varName)
        {
            if(!isset($oldValues[$varName]))
                $oldValues[$varName] = NULL;

            if($newValues[$varName] !== $oldValues[$varName] OR $action === self::CREATE)
                HistoryDatabaseHandler::insertHistoryItem($record->getId(), $varName, $oldValues[$varName], $newValues[$varName]);
        }

        return;
    }
}