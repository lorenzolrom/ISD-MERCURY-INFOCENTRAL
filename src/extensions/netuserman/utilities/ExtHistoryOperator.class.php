<?php


namespace extensions\netuserman\utilities;

use database\HistoryDatabaseHandler;
use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use exceptions\LDAPException;
use extensions\netuserman\business\NetGroupOperator;
use extensions\netuserman\business\NetUserOperator;

class ExtHistoryOperator
{
    /**
     * @param string $tableName
     * @param string $index
     * @param string $action
     * @param string $username
     * @return array
     * @throws EntryNotFoundException
     * @throws DatabaseException
     */
    public static function getHistory(string $tableName, string $index = "%", string $action = '%', string $username = '%'): array
    {
        if($index !== "" AND $index !== "%") // only check if index is set
        {
            if($tableName == '!NETUSER') // Convert username to GUID
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
        }

        return HistoryDatabaseHandler::select($tableName, $index, $action, $username);
    }
}