<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/15/2019
 * Time: 6:45 AM
 */


namespace database\itsm;


use database\DatabaseConnection;
use database\DatabaseHandler;

class ReturnOrderDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $warehouseId
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function doReturnOrdersReferenceWarehouse(int $warehouseId): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `ITSM_ReturnOrder` WHERE `warehouse` = ? LIMIT 1");
        $select->bindParam(1, $warehouseId, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }
}