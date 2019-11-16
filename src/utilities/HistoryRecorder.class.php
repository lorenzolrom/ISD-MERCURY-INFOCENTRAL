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
     * @param array|null $nullVars
     * @param bool $noUser Should this history entry be done by NO user?
     * @return int
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function writeHistory(string $tableName, string $action, string $index, Model $currentState, array $newValues = array(), array $nullVars = array(), bool $noUser = FALSE): int
    {
        $rawOldValues = (array)$currentState;
        $oldValues = array();

        // Format name of old variables
        foreach(array_keys($rawOldValues) as $varName)
        {
            $parts =  explode(str_split($varName)[0], $varName);
            $shortVarName = $parts[sizeof($parts) - 1];

            $oldValues[$shortVarName] = $rawOldValues[$varName];
        }

        // Create record for entry
        if($noUser)
        {
            $username = NULL;
        }
        else
        {
            $username = CurrentUserController::currentUser()->getUsername();
        }

        $record = HistoryDatabaseHandler::insert($tableName, $action, $index, $username, date('Y-m-d H:i:s'));

        foreach(array_keys($oldValues) as $varName)
        {
            if($varName === 'password') // do not write password to history
                continue;

            if(!isset($newValues[$varName]) AND !in_array($varName, $nullVars))
                $newValues[$varName] = $oldValues[$varName]; // ignore unchanged items

            if($newValues[$varName] != $oldValues[$varName] OR $action === self::CREATE)
                HistoryDatabaseHandler::insertHistoryItem($record->getId(), $varName, $oldValues[$varName], $newValues[$varName]);
        }

        return $record->getId();
    }

    /**
     * @param int $history
     * @param array $vals
     * @throws \exceptions\DatabaseException
     */
    public static function writeAssocHistory(int $history, array $vals = array()): void
    {
        foreach(array_keys($vals) as $param)
        {
            if(!is_array($vals[$param]))
                continue;

            foreach($vals[$param] as $val)
            {
                HistoryDatabaseHandler::insertHistoryItem($history, $param, '', $val);
            }
        }

        return;
    }
}