<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/15/2019
 * Time: 8:46 AM
 */


namespace business\itsm;


use business\AttributeOperator;
use business\Operator;
use database\itsm\PurchaseOrderDatabaseHandler;
use database\itsm\VendorDatabaseHandler;
use database\itsm\WarehouseDatabaseHandler;
use models\itsm\Commodity;
use models\itsm\PurchaseOrder;
use utilities\HistoryRecorder;

class PurchaseOrderOperator extends Operator
{
    /**
     * @param int $id
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function numberFromId(int $id): ?int
    {
        return PurchaseOrderDatabaseHandler::numberFromId($id);
    }

    /**
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function create(array $vals): array
    {
        $errors = self::validate('models\itsm\PurchaseOrder', $vals);
        if(!empty($errors))
            return array('errors' => $errors);

        $po = PurchaseOrderDatabaseHandler::insert(PurchaseOrderDatabaseHandler::nextNumber(), $vals['orderDate'],
            WarehouseDatabaseHandler::selectIdFromCode($vals['warehouse']),
            VendorDatabaseHandler::selectIdFromCode($vals['vendor']),
            AttributeOperator::idFromCode('itsm', 'post', 'rdts'), (string)$vals['notes']);

        HistoryRecorder::writeHistory('ITSM_PurchaseOrder', HistoryRecorder::CREATE, $po->getId(), $po);

        return array('id' => $po->getId());
    }

    /**
     * @param PurchaseOrder $po
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function update(PurchaseOrder $po, array $vals): array
    {
        // Cannot edit received or canceled P.O.
        if($po->getReceiveDate() !== NULL OR $po->getCancelDate() !== NULL)
            return array('errors' => array('Cannot edit closed Purchase Order'));

        $errors = self::validate('models\itsm\PurchaseOrder', $vals);
        if(!empty($errors))
            return array('errors' => $errors);

        HistoryRecorder::writeHistory('ITSM_PurchaseOrder', HistoryRecorder::MODIFY, $po->getId(), $po, $vals);

        $po = PurchaseOrderDatabaseHandler::update($po->getId(), $vals['orderDate'],
            WarehouseDatabaseHandler::selectIdFromCode($vals['warehouse']),
            VendorDatabaseHandler::selectIdFromCode($vals['vendor']),
            $po->getStatus(), (string)$vals['notes'], $po->getSent(), $po->getSendDate(), $po->getReceived(),
            $po->getReceiveDate(), $po->getCanceled(), $po->getCancelDate());

        return array('id' => $po->getId());
    }

    public static function addCommodity(PurchaseOrder $po, Commodity $commodity, int $quantity, float $unitCost): array
    {
        // Cannot edit P.O. that has been sent
        if($po->getSent() === 1)
            return array('errors' => array('Cannot edit commodities of sent Purchase Order'));
    }

    public static function removeCommodity(PurchaseOrder $po, int $id): array
    {
        // Cannot edit P.O. that has been sent
        if($po->getSent() === 1)
            return array('errors' => array('Cannot edit commodities of sent Purchase Order'));
    }

    public static function addCost(PurchaseOrder $po, float $cost, string $notes): array
    {
        // Cannot edit P.O. that has been sent
        if($po->getSent() === 1)
            return array('errors' => array('Cannot edit cost items of sent Purchase Order'));
    }

    public static function removeCost(PurchaseOrder $po, int $id): array
    {
        // Cannot edit P.O. that has been sent
        if($po->getSent() === 1)
            return array('errors' => array('Cannot edit cost items of sent Purchase Order'));
    }

    public static function send(PurchaseOrder $po): array
    {
        // Cannot sent PO that has already been sent
        if($po->getSent() === 1)
            return array('errors' => array('Purchase Order has already been sent'));

        // TODO: Cannot sent PO with no commodities
    }

    public static function receive(PurchaseOrder $po, string $receiveDate, ?int $startAssetTag): array
    {
        // Cannot receive PO that has not been sent
        if($po->getSent() === 0)
            return array('errors' => array('Purchase Order has not been sent'));

        // Cannot receive PO that has been canceled
        if($po->getCanceled() === 0)
            return array('errors' => array('Purchase Order has been canceled'));
    }

    public static function cancel(PurchaseOrder $po): array
    {
        // Cannot cancel PO that has been received
        if($po->getSent() === 0)
            return array('errors' => array('Purchase Order has not been sent'));

        if($po->getReceived() === 1)
            return array('errors' => array('Cannot cancel received Purchase Order'));
    }
}