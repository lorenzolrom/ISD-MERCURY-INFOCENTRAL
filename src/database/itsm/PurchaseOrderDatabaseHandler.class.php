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
use exceptions\EntryNotFoundException;
use models\itsm\PurchaseOrder;

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

    /**
     * @param int $id
     * @return PurchaseOrder
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): PurchaseOrder
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id`, `number`, `orderDate`, `warehouse`, `vendor`, `status`, `notes`, 
              `sent`, `sendDate`, `received`, `receiveDate`, `cancelDate`, `canceled` FROM `ITSM_PurchaseOrder` 
              WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('models\itsm\PurchaseOrder');
    }

    /**
     * @param int $number
     * @return PurchaseOrder
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectByNumber(int $number): PurchaseOrder
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `ITSM_PurchaseOrder` WHERE `number` = ? LIMIT 1');
        $select->bindParam(1, $number, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        return self::selectById($select->fetchColumn());
    }

    /**
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function nextNumber(): int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `number` FROM `ITSM_PurchaseOrder` ORDER BY `number` DESC LIMIT 1');
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 0 ? 1 : $select->fetchColumn() + 1;
    }

    /**
     * @param int $id
     * @param string $orderDate
     * @param int $warehouse
     * @param int $vendor
     * @param int $status
     * @param string $notes
     * @param int $sent
     * @param string|null $sendDate
     * @param int $received
     * @param string|null $receiveDate
     * @param int $canceled
     * @param string|null $cancelDate
     * @return PurchaseOrder
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $orderDate, int $warehouse, int $vendor, int $status, string $notes,
                                  int $sent, ?string $sendDate, int $received, ?string $receiveDate, int $canceled,
                                  ?string $cancelDate): PurchaseOrder
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `ITSM_PurchaseOrder` SET `orderDate` = :orderDate, 
                                `warehouse` = :warehouse, `vendor` = :vendor, `status` = :status, `notes` = :notes, 
                                `sent` = :sent, `sendDate` = :sendDate, `received` = :received, 
                                `receiveDate` = :receiveDate, `canceled` = :canceled, `cancelDate` = :cancelDate WHERE `id` = :id');

        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->bindParam('orderDate', $orderDate, DatabaseConnection::PARAM_STR);
        $update->bindParam('warehouse', $warehouse, DatabaseConnection::PARAM_INT);
        $update->bindParam('vendor', $vendor, DatabaseConnection::PARAM_INT);
        $update->bindParam('status', $status, DatabaseConnection::PARAM_INT);
        $update->bindParam('notes', $notes, DatabaseConnection::PARAM_STR);
        $update->bindParam('sent', $sent, DatabaseConnection::PARAM_INT);
        $update->bindParam('sendDate', $sendDate, DatabaseConnection::PARAM_STR);
        $update->bindParam('received', $received, DatabaseConnection::PARAM_INT);
        $update->bindParam('receiveDate', $receiveDate, DatabaseConnection::PARAM_STR);
        $update->bindParam('canceled', $canceled, DatabaseConnection::PARAM_INT);
        $update->bindParam('cancelDate', $cancelDate, DatabaseConnection::PARAM_STR);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $number
     * @param string $orderDate
     * @param int $warehouse
     * @param int $vendor
     * @param int $status
     * @param string $notes
     * @return PurchaseOrder
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $number, string $orderDate, int $warehouse, int $vendor, int $status, string $notes): PurchaseOrder
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `ITSM_PurchaseOrder` (`number`, `orderDate`, `warehouse`, 
                                  `vendor`, `status`, `notes`) VALUES (:number, :orderDate, :warehouse, :vendor, :status, :notes)');
        $insert->bindParam('number', $number, DatabaseConnection::PARAM_INT);
        $insert->bindParam('orderDate', $orderDate, DatabaseConnection::PARAM_STR);
        $insert->bindParam('warehouse', $warehouse, DatabaseConnection::PARAM_INT);
        $insert->bindParam('vendor', $vendor, DatabaseConnection::PARAM_INT);
        $insert->bindParam('status', $status, DatabaseConnection::PARAM_INT);
        $insert->bindParam('notes', $notes, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param int $commodity
     * @param int $quantity
     * @param float $unitCost
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function addCommodity(int $id, int $commodity, int $quantity, float $unitCost): int
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `ITSM_PurchaseOrder_Commodity` (`purchaseOrder`, `commodity`, `quantity`, `unitCost`) VALUES (:purchaseOrder, :commodity, :quantity, :unitCost)');
        $insert->bindParam('purchaseOrder', $id, DatabaseConnection::PARAM_INT);
        $insert->bindParam('commodity', $commodity, DatabaseConnection::PARAM_INT);
        $insert->bindParam('quantity', $quantity, DatabaseConnection::PARAM_INT);
        $insert->bindParam('unitCost', $unitCost, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return $id;
    }

    /**
     * @param int $purchaseOrder
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function removeCommodity(int $purchaseOrder, int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `ITSM_PurchaseOrder_Commodity` WHERE `id` = :id AND `purchaseOrder` = :purchaseOrder');
        $delete->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $delete->bindParam('purchaseOrder', $purchaseOrder, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param float $cost
     * @param string $notes
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function addCostItem(int $id, float $cost, string $notes): int
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `ITSM_PurchaseOrder_CostItem` (`purchaseOrder`, `cost`, `notes`) VALUES (:purchaseOrder, :cost, :notes)');
        $insert->bindParam('purchaseOrder', $id, DatabaseConnection::PARAM_INT);
        $insert->bindParam('cost', $cost, DatabaseConnection::PARAM_STR);
        $insert->bindParam('notes', $notes, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return $id;
    }

    /**
     * @param int $purchaseOrder
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function removeCostItem(int $purchaseOrder, int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `ITSM_PurchaseOrder_CostItem` WHERE `id` = :id AND `purchaseOrder` = :purchaseOrder');
        $delete->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $delete->bindParam('purchaseOrder', $purchaseOrder, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }
}