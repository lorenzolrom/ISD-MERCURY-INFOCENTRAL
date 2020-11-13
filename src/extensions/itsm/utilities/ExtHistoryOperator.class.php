<?php


namespace extensions\itsm\utilities;

use database\HistoryDatabaseHandler;
use exceptions\DatabaseException;
use extensions\itsm\business\ApplicationOperator;
use extensions\itsm\business\AssetOperator;
use extensions\itsm\business\DiscardOrderOperator;
use extensions\itsm\business\PurchaseOrderOperator;

/**
 * Class ExtHistoryOperator
 * @package extensions\itsm\utilities
 */
class ExtHistoryOperator
{
    /**
     * @param string $tableName
     * @param string $index
     * @param string $action
     * @param string $username
     * @return array
     * @throws DatabaseException
     */
    public static function getHistory(string $tableName, string $index = "%", string $action = '%', string $username = '%'): array
    {
        if($index !== "" AND $index !== "%") // only check if index is set
        {
            $index = (int)$index;
            if($tableName == 'ITSM_Asset') // Assets use their asset tags as 'primary' keys
                $index = (string)AssetOperator::idFromAssetTag($index);
            else if($tableName == 'ITSM_Application') // Convert Number to ID
                $index = (string)ApplicationOperator::idFromNumber($index);
            else if($tableName == 'ITSM_PurchaseOrder') // Convert Number to ID
                $index = (string)PurchaseOrderOperator::idFromNumber($index);
            else if($tableName == 'ITSM_DiscardOrder') // Convert Number to ID
                $index = (string)DiscardOrderOperator::idFromNumber($index);
        }

        return HistoryDatabaseHandler::select($tableName, $index, $action, $username);
    }
}