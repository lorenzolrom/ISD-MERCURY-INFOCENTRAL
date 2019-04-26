<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/15/2019
 * Time: 6:43 AM
 */


namespace database\itsm;


use database\DatabaseConnection;
use database\DatabaseHandler;

class PurchaseOrderDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $warehouseId
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function doPurchaseOrdersReferenceWarehouse(int $warehouseId): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `ITSM_PurchaseOrder` WHERE `warehouse` = ? LIMIT 1");
        $select->bindParam(1, $warehouseId, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $vendorId
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function doPurchaseOrdersReferenceVendor(int $vendorId): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `ITSM_PurchaseOrder` WHERE `vendor` = ? LIMIT 1');
        $select->bindParam(1, $vendorId, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function numberFromId(int $id): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `number` FROM `ITSM_PurchaseOrder` WHERE `id` = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            return NULL;

        return $select->fetchColumn();
    }
}