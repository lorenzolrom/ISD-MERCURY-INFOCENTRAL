<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/31/2019
 * Time: 11:31 AM
 */


namespace extensions\itsm\business;


use business\Operator;
use extensions\itsm\database\DiscardOrderDatabaseHandler;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use extensions\itsm\models\DiscardOrder;
use utilities\HistoryRecorder;

class DiscardOrderOperator extends Operator
{
    /**
     * @param int $id
     * @return DiscardOrder
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getById(int $id): DiscardOrder
    {
        return DiscardOrderDatabaseHandler::selectById($id);
    }

    /**
     * @param int $number
     * @return DiscardOrder
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function getByNumber(int $number): DiscardOrder
    {
        return DiscardOrderDatabaseHandler::selectByNumber($number);
    }

    /**
     * @param string $number
     * @param string $startDate
     * @param string $endDate
     * @param array|null $approved
     * @param array|null $fulfilled
     * @param array|null $canceled
     * @return DiscardOrder[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(string $number = '%', string $startDate = '1000-01-01', $endDate = '9999-12-31',
                                  ?array $approved = NULL, ?array $fulfilled = NULL, ?array $canceled = NULL): array
    {
        return DiscardOrderDatabaseHandler::select($number, $startDate, $endDate, $approved, $fulfilled, $canceled);
    }

    /**
     * @param string|null $notes
     * @return int
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function create(?string $notes): int
    {
        $do = DiscardOrderDatabaseHandler::insert(DiscardOrderDatabaseHandler::nextNumber(), date('Y-m-d'), (string) $notes);

        HistoryRecorder::writeHistory('ITSM_DiscardOrder', HistoryRecorder::CREATE, $do->getId(), $do);

        return $do->getNumber();
    }

    /**
     * @param DiscardOrder $do
     * @param string|null $notes
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function update(DiscardOrder $do, ?string $notes): bool
    {
        HistoryRecorder::writeHistory('ITSM_DiscardOrder', HistoryRecorder::MODIFY, $do->getId(), $do, array('notes' => $notes));
        DiscardOrderDatabaseHandler::update($do->getId(), (string)$notes, $do->getApproved(), $do->getApproveDate(), $do->getFulfilled(), $do->getFulfillDate(), $do->getCanceled(), $do->getCancelDate());

        return TRUE;
    }

    /**
     * @param DiscardOrder $do
     * @param string|null $assetTag
     * @return bool
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function addAsset(DiscardOrder $do, ?string $assetTag): bool
    {
        if($do->getApproved() === 1)
            throw new ValidationError(array('Cannot modify approved Discard Order'));

        $asset = NULL;

        try
        {
            $asset = AssetOperator::getAsset((int)$assetTag);
        }
        catch(EntryNotFoundException $e)
        {
            throw new ValidationError(array('Asset not found'), $e);
        }

        if($asset->getDiscarded())
            throw new ValidationError(array('Asset has already been discarded'));
        if(DiscardOrderDatabaseHandler::assetOnOrder($asset->getId()))
            throw new ValidationError(array('Asset is already on a discard order'));

        $hist = HistoryRecorder::writeHistory('ITSM_DiscardOrder', HistoryRecorder::MODIFY, $do->getId(), $do);
        HistoryRecorder::writeAssocHistory($hist, array(
            'addAsset' => array($asset->getId())
        ));

        return DiscardOrderDatabaseHandler::addAsset($do->getId(), $asset->getId());
    }

    /**
     * @param DiscardOrder $do
     * @param string|null $assetTag
     * @return bool
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function removeAsset(DiscardOrder $do, ?string $assetTag): bool
    {
        if($do->getApproved() === 1)
            throw new ValidationError(array('Cannot modify approved Discard Order'));

        try
        {
            $asset = AssetOperator::getAsset((int)$assetTag);
        }
        catch(EntryNotFoundException $e)
        {
            throw new ValidationError(array('Asset not found'), $e);
        }

        $hist = HistoryRecorder::writeHistory('ITSM_DiscardOrder', HistoryRecorder::MODIFY, $do->getId(), $do);
        HistoryRecorder::writeAssocHistory($hist, array(
            'removeAsset' => array($asset->getId())
        ));

        return DiscardOrderDatabaseHandler::removeAsset($do->getId(), $asset->getId());
    }

    /**
     * @param DiscardOrder $do
     * @return bool
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function approve(DiscardOrder $do): bool
    {
        if($do->getApproved() === 1)
            throw new ValidationError(array('Discard Order has already been approved'));

        if($do->getCanceled() === 1)
            throw new ValidationError(array('Discard Order has been canceled'));

        $date = date('Y-m-d');

        HistoryRecorder::writeHistory('ITSM_DiscardOrder', HistoryRecorder::MODIFY, $do->getId(), $do, array(
            'approved' => 1,
            'approveDate' => $date
        ));

        DiscardOrderDatabaseHandler::update($do->getId(), $do->getNotes(), 1, $date, $do->getFulfilled(), $do->getFulfillDate(), $do->getCanceled(), $do->getCancelDate());
        return TRUE;
    }

    /**
     * @param DiscardOrder $do
     * @return bool
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function fulfill(DiscardOrder $do): bool
    {
        if($do->getApproved() === 0)
            throw new ValidationError(array('Discard Order has not been approved'));

        if($do->getCanceled() === 1)
            throw new ValidationError(array('Discard Order has been canceled'));

        $date = date('Y-m-d');

        HistoryRecorder::writeHistory('ITSM_DiscardOrder', HistoryRecorder::MODIFY, $do->getId(), $do, array(
            'fulfilled' => 1,
            'fulfillDate' => $date
        ));

        DiscardOrderDatabaseHandler::update($do->getId(), $do->getNotes(), $do->getApproved(), $do->getApproveDate(), 1, $date, $do->getCanceled(), $do->getCancelDate());

        foreach($do->getAssets() as $asset)
        {
            AssetOperator::discard($asset, $do->getId());
        }

        return TRUE;
    }

    /**
     * @param DiscardOrder $do
     * @return bool
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function cancel(DiscardOrder $do): bool
    {
        if($do->getFulfilled() === 1)
            throw new ValidationError(array('Discard Order has been fulfilled'));

        $date = date('Y-m-d');

        HistoryRecorder::writeHistory('ITSM_DiscardOrder', HistoryRecorder::MODIFY, $do->getId(), $do, array(
            'canceled' => 1,
            'cancelDate' => $date
        ));

        DiscardOrderDatabaseHandler::removeAllAssets($do->getId());
        DiscardOrderDatabaseHandler::update($do->getId(), $do->getNotes(), $do->getApproved(), $do->getApproveDate(), $do->getFulfilled(), $do->getFulfillDate(), 1, $date);
        return TRUE;
    }

    /**
     * @param int $number
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function idFromNumber(int $number): ?int
    {
        return DiscardOrderDatabaseHandler::selectIdByNumber($number);
    }

    /**
     * @param int $id
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function numberFromId(int $id): ?int
    {
        return DiscardOrderDatabaseHandler::selectNumberById($id);
    }
}
