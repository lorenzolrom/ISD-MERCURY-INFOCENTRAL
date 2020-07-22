<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/15/2019
 * Time: 8:46 AM
 */


namespace extensions\itsm\business;


use business\AttributeOperator;
use business\Operator;
use database\AttributeDatabaseHandler;
use extensions\itsm\database\PurchaseOrderDatabaseHandler;
use extensions\itsm\database\VendorDatabaseHandler;
use extensions\itsm\database\WarehouseDatabaseHandler;
use exceptions\ValidationError;
use models\Attribute;
use extensions\itsm\models\PurchaseOrder;
use utilities\HistoryRecorder;
use utilities\Validator;

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
     * @param int $number
     * @return PurchaseOrder
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getPO(int $number): PurchaseOrder
    {
        return PurchaseOrderDatabaseHandler::selectByNumber($number);
    }

    /**
     * @param string $number
     * @param string $vendor
     * @param string $warehouse
     * @param string $orderStart
     * @param string $orderEnd
     * @param array|null $status
     * @return PurchaseOrder[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(string $number = '%', string $vendor = '%', string $warehouse = '%',
                                  string $orderStart = '1000-01-01', string $orderEnd = '9999-12-31', ?array $status = NULL): array
    {
        return PurchaseOrderDatabaseHandler::select($number, $vendor, $warehouse, $orderStart, $orderEnd, $status);
    }

    /**
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function create(array $vals): array
    {
        self::validate('extensions\itsm\models\PurchaseOrder', $vals);

        $po = PurchaseOrderDatabaseHandler::insert(PurchaseOrderDatabaseHandler::nextNumber(), $vals['orderDate'],
            WarehouseDatabaseHandler::selectIdFromCode($vals['warehouse']),
            VendorDatabaseHandler::selectIdFromCode($vals['vendor']),
            AttributeOperator::idFromCode('itsm', 'post', 'rdts'), (string)$vals['notes']);

        HistoryRecorder::writeHistory('ITSM_PurchaseOrder', HistoryRecorder::CREATE, $po->getId(), $po);

        return array('id' => $po->getNumber());
    }

    /**
     * @param PurchaseOrder $po
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function update(PurchaseOrder $po, array $vals): array
    {
        // Cannot edit received or canceled P.O.
        if($po->getReceiveDate() !== NULL OR $po->getCancelDate() !== NULL)
            throw new ValidationError(array('Cannot edit closed Purchase Order'));

        self::validate('extensions\itsm\models\PurchaseOrder', $vals);

        $vals['warehouse'] = WarehouseDatabaseHandler::selectIdFromCode($vals['warehouse']);
        $vals['vendor'] = VendorDatabaseHandler::selectIdFromCode($vals['vendor']);

        HistoryRecorder::writeHistory('ITSM_PurchaseOrder', HistoryRecorder::MODIFY, $po->getId(), $po, $vals);

        $po = PurchaseOrderDatabaseHandler::update($po->getId(), $vals['orderDate'],
            $vals['warehouse'],
            $vals['vendor'],
            $po->getStatus(), (string)$vals['notes'], $po->getSent(), $po->getSendDate(), $po->getReceived(),
            $po->getReceiveDate(), $po->getCanceled(), $po->getCancelDate());

        return array('id' => $po->getNumber());
    }

    /**
     * @param PurchaseOrder $po
     * @param string $commodity
     * @param int $quantity
     * @param float $unitCost
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function addCommodity(PurchaseOrder $po, string $commodity, int $quantity, float $unitCost): array
    {
        // Cannot edit P.O. that has been sent
        if($po->getSent() === 1)
            throw new ValidationError(array('Cannot edit commodities of sent Purchase Order'));

        if($po->getCanceled() === 1)
            throw new ValidationError(array('Purchase Order has been canceled'));

        self::validate('extensions\itsm\models\PurchaseOrderCommodity', array('commodity' => $commodity, 'quantity' => $quantity, 'unitCost' => $unitCost));

        $commodity = CommodityOperator::getCommodityByCode($commodity);

        $history = HistoryRecorder::writeHistory('ITSM_PurchaseOrder', HistoryRecorder::MODIFY, $po->getId(), $po);
        HistoryRecorder::writeAssocHistory($history, array(
            'addCommodity' => array($commodity->getId()),
            'quantity' => array($quantity),
            'unitCost' => array($unitCost)
        ));

        return array('id' => PurchaseOrderDatabaseHandler::addCommodity($po->getId(), $commodity->getId(), (int)$quantity, (float)$unitCost));
    }

    /**
     * @param PurchaseOrder $po
     * @param int $id
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws ValidationError
     */
    public static function removeCommodity(PurchaseOrder $po, int $id): array
    {
        // Cannot edit P.O. that has been sent
        if($po->getSent() === 1)
            throw new ValidationError(array('Cannot edit commodities of sent Purchase Order'));

        if($po->getCanceled() === 1)
            throw new ValidationError(array('Purchase Order has been canceled'));

        $commodity = PurchaseOrderDatabaseHandler::selectCommodityById($id);

        $history = HistoryRecorder::writeHistory('ITSM_PurchaseOrder', HistoryRecorder::MODIFY, $po->getId(), $po);
        HistoryRecorder::writeAssocHistory($history, array(
            'removeCommodity' => array($commodity->getId()),
            'quantity' => array($commodity->getQuantity()),
            'unitCost' => array($commodity->getUnitCost())
        ));

        PurchaseOrderDatabaseHandler::removeCommodity($po->getId(), $id);

        return array();
    }

    /**
     * @param PurchaseOrder $po
     * @param float $cost
     * @param string $notes
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function addCost(PurchaseOrder $po, float $cost, string $notes): array
    {
        // Cannot edit P.O. that has been sent
        if($po->getSent() === 1)
            throw new ValidationError(array('Cannot edit cost items of sent Purchase Order'));

        if($po->getCanceled() === 1)
            throw new ValidationError(array('Purchase Order has been canceled'));

        self::validate('extensions\itsm\models\PurchaseOrderCostItem', array('cost' => $cost));

        $history = HistoryRecorder::writeHistory('ITSM_PurchaseOrder', HistoryRecorder::MODIFY, $po->getId(), $po);
        HistoryRecorder::writeAssocHistory($history, array(
            'addCostItem' => array((float)$cost),
            'notes' => array($notes)
        ));

        return array('id' => PurchaseOrderDatabaseHandler::addCostItem($po->getId(), (float) $cost, $notes));
    }

    /**
     * @param PurchaseOrder $po
     * @param int $id
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws ValidationError
     */
    public static function removeCost(PurchaseOrder $po, int $id): array
    {
        // Cannot edit P.O. that has been sent
        if($po->getSent() === 1)
            throw new ValidationError(array('Cannot edit cost items of sent Purchase Order'));

        if($po->getCanceled() === 1)
            throw new ValidationError(array('Purchase Order has been canceled'));

        $cost = PurchaseOrderDatabaseHandler::selectCostItemById($id);

        $history = HistoryRecorder::writeHistory('ITSM_PurchaseOrder', HistoryRecorder::MODIFY, $po->getId(), $po);
        HistoryRecorder::writeAssocHistory($history, array(
            'removeCostItem' => array($cost->getId())
        ));

        PurchaseOrderDatabaseHandler::removeCostItem($po->getId(), $id);

        return array();
    }

    /**
     * @param PurchaseOrder $po
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws ValidationError
     */
    public static function send(PurchaseOrder $po): array
    {
        // Cannot sent PO that has already been sent
        if($po->getSent() === 1)
            throw new ValidationError(array('Purchase Order has already been sent'));

        if($po->getCanceled() === 1)
            throw new ValidationError(array('Purchase Order has been canceled'));

        if(sizeof($po->getCommodities()) < 1)
            throw new ValidationError(array('Purchase Order cannot be sent without at least one commodity'));

        $sendDate = date('Y-m-d');
        $status = AttributeOperator::idFromCode('itsm', 'post', 'sent');
        $newVals = array('sent' => 1, 'sendDate' => $sendDate, 'status' => $status);

        HistoryRecorder::writeHistory('ITSM_PurchaseOrder', HistoryRecorder::MODIFY, $po->getId(), $po, $newVals);

        PurchaseOrderDatabaseHandler::update($po->getId(), $po->getOrderDate(), $po->getWarehouse(), $po->getVendor(),
            $status, $po->getNotes(), 1, $sendDate, $po->getReceived(), $po->getReceiveDate(), $po->getCanceled(), $po->getCancelDate());

        return array('id' => $po->getId());
    }

    /**
     * @param PurchaseOrder $po
     * @param string $receiveDate
     * @param int|null $startAssetTag
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws ValidationError
     */
    public static function receive(PurchaseOrder $po, string $receiveDate, ?int $startAssetTag = NULL): array
    {
        // Cannot receive PO that has not been sent
        if($po->getSent() === 0)
            throw new ValidationError(array('Purchase Order has not been sent'));

        if($po->getReceived() === 1)
            throw new ValidationError(array('Purchase Order has already been received'));

        // Cannot receive PO that has been canceled
        if($po->getCanceled() === 1)
            throw new ValidationError(array('Purchase Order has been canceled'));


        if(!Validator::validDate($receiveDate))
            throw new ValidationError(array('Receive Date is not valid'));

        $commodities = $po->getCommodities();

        $receivedCommodities = array(); // Store actual received commodities

        $status = AttributeOperator::idFromCode('itsm', 'post', 'rcvf'); // Received in full

        foreach($commodities as $commodity)
        {
            $receivedCommodities[] = array($commodity->getCommodity(), $commodity->getQuantity());
        }

        // Create assets
        $assetTags = array();

        foreach($receivedCommodities as $receivedCommodity)
        {
            for($i = 0; $i < $receivedCommodity[1]; $i++)
            {
                if($startAssetTag !== NULL AND $startAssetTag !== '' AND is_numeric($startAssetTag))
                {
                    $assetTag = $startAssetTag;
                    $startAssetTag++; // Increment start asset tag by one
                }
                else
                {
                    $assetTag = AssetOperator::nextAssetTag();
                }

                if(AssetOperator::idFromAssetTag($assetTag) !== NULL) // If an asset tag conflict arises, use the next available tag
                    $assetTag = AssetOperator::nextAssetTag();

                $asset = AssetOperator::createAsset($receivedCommodity[0], $po->getWarehouse(), $assetTag, $po->getId());
                $assetTags[] = $asset->getAssetTag();
            }
        }

        $newVals = array('receiveDate' => $receiveDate, 'received' => 1, 'status' => $status);

        HistoryRecorder::writeHistory('ITSM_PurchaseOrder', HistoryRecorder::MODIFY, $po->getId(), $po, $newVals);

        PurchaseOrderDatabaseHandler::update($po->getId(), $po->getOrderDate(), $po->getWarehouse(), $po->getVendor(),
            $status, $po->getNotes(), $po->getSent(), $po->getSendDate(), 1, $receiveDate, $po->getCanceled(),
            $po->getCancelDate());

        return $assetTags;
    }

    /**
     * @param PurchaseOrder $po
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws ValidationError
     */
    public static function cancel(PurchaseOrder $po): array
    {
        // Cannot cancel PO that has been received
        if($po->getReceived() === 1)
            throw new ValidationError(array('Cannot cancel received Purchase Order'));

        if($po->getCanceled() === 1)
            throw new ValidationError(array('Purchase Order has already been canceled'));

        $status = AttributeOperator::idFromCode('itsm', 'post', 'cncl');
        $cancelDate = date('Y-m-d');
        $newVals = array('status' => $status, 'canceled' => 1, 'cancelDate' => $cancelDate);

        HistoryRecorder::writeHistory('ITSM_PurchaseOrder', HistoryRecorder::MODIFY, $po->getId(), $po, $newVals);

        PurchaseOrderDatabaseHandler::update($po->getId(), $po->getOrderDate(), $po->getWarehouse(), $po->getVendor(),
            $status, $po->getNotes(), $po->getSent(), $po->getSendDate(), $po->getReceived(),
            $po->getReceiveDate(), 1, $cancelDate);

        return array();
    }

    /**
     * Get PO status codes
     * @return Attribute[]
     * @throws \exceptions\DatabaseException
     */
    public static function getStatuses(): array
    {
        return AttributeDatabaseHandler::select('itsm', 'post');
    }

    /**
     * @param string $number
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function idFromNumber(string $number):?int
    {
        return PurchaseOrderDatabaseHandler::selectIdByNumber((int)$number);
    }
}
