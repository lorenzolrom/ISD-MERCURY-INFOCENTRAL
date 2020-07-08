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
 * Time: 11:04 AM
 */


namespace extensions\itsm\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\itsm\models\Asset;
use extensions\itsm\models\DiscardOrder;
use utilities\Validator;

class DiscardOrderDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return DiscardOrder
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): DiscardOrder
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id`, `number`, `date`, `notes`, `approved`, `approveDate`, 
          `fulfilled`, `fulfillDate`, `canceled`, `cancelDate` FROM `ITSM_DiscardOrder` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('extensions\itsm\models\DiscardOrder');
    }

    /**
     * @param int $number
     * @return DiscardOrder
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectByNumber(int $number): DiscardOrder
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `ITSM_DiscardOrder` WHERE `number` = ? LIMIT 1');
        $select->bindParam(1, $number, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        return self::selectById($select->fetchColumn());
    }

    /**
     * @param int $number
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectIdByNumber(int $number): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `ITSM_DiscardOrder` WHERE `number` = ? LIMIT 1');
        $select->bindParam(1, $number, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1 ? $select->fetchColumn() : NULL;
    }

    /**
     * @param int $id
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectNumberById(int $id): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `number` FROM `ITSM_DiscardOrder` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1 ? $select->fetchColumn() : NULL;
    }

    /**
     * @param string $number
     * @param string $startDate
     * @param string $endDate
     * @param array|null $approved
     * @param array|null $fulfilled
     * @param array|null $canceled
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $number, string $startDate, string $endDate, ?array $approved = NULL, ?array $fulfilled = NULL,
                                  ?array $canceled = NULL): array
    {
        if(!Validator::validDate($endDate))
            $endDate = '9999-12-31';
        if(!Validator::validDate($startDate))
            $startDate = '1000-01-01';

        $query = 'SELECT `id` FROM `ITSM_DiscardOrder` WHERE `number` LIKE :number AND `date` BETWEEN :startDate AND :endDate';

        if($approved !== NULL AND !empty($approved))
            $query .= ' AND `approved` IN (' . self::getBooleanString($approved) . ')';
        if($fulfilled !== NULL AND !empty($fulfilled))
            $query .= ' AND `fulfilled` IN (' . self::getBooleanString($fulfilled) . ')';
        if($canceled !== NULL AND !empty($canceled))
            $query .= ' AND `canceled` IN (' . self::getBooleanString($canceled) . ')';

        $handler = new DatabaseConnection();

        $select = $handler->prepare($query);
        $select->bindParam('number', $number, DatabaseConnection::PARAM_STR);
        $select->bindParam('startDate', $startDate, DatabaseConnection::PARAM_STR);
        $select->bindParam('endDate', $endDate, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $orders = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$orders[] = self::selectById($id);}
            catch(EntryNotFoundException $e){}
        }

        return $orders;
    }

    /**
     * @param int $number
     * @param string $date
     * @param string $notes
     * @return DiscardOrder
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $number, string $date, string $notes): DiscardOrder
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `ITSM_DiscardOrder` (`number`, `date`, `notes`) VALUES (:number, :date, :notes)');
        $insert->bindParam('number', $number, DatabaseConnection::PARAM_INT);
        $insert->bindParam('date', $date, DatabaseConnection::PARAM_STR);
        $insert->bindParam('notes', $notes, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function nextNumber(): int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `number` FROM `ITSM_DiscardOrder` ORDER BY `number` DESC LIMIT 1');
        $select->execute();

        $handler->close();

        return $select->getRowCount() !== 1 ? 1 : ($select->fetchColumn() + 1);
    }

    /**
     * @param int $id
     * @param string $notes
     * @param int $approved
     * @param string|null $approveDate
     * @param int $fulfilled
     * @param string|null $fulfillDate
     * @param int $canceled
     * @param string|null $cancelDate
     * @return DiscardOrder
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $notes, int $approved, ?string $approveDate, int $fulfilled,
                                  ?string $fulfillDate, int $canceled, ?string $cancelDate): DiscardOrder
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `ITSM_DiscardOrder` SET `notes` = :notes, `approved` = :approved, 
                               `approveDate` = :approveDate, `fulfilled` = :fulfilled, `fulfillDate` = :fulfillDate, 
                               `canceled` = :canceled, `cancelDate` = :cancelDate WHERE `id` = :id');
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->bindParam('notes', $notes, DatabaseConnection::PARAM_STR);
        $update->bindParam('approved', $approved, DatabaseConnection::PARAM_INT);
        $update->bindParam('approveDate', $approveDate, DatabaseConnection::PARAM_STR);
        $update->bindParam('fulfilled', $fulfilled, DatabaseConnection::PARAM_INT);
        $update->bindParam('fulfillDate', $fulfillDate, DatabaseConnection::PARAM_STR);
        $update->bindParam('canceled', $canceled, DatabaseConnection::PARAM_INT);
        $update->bindParam('cancelDate', $cancelDate, DatabaseConnection::PARAM_STR);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $asset
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function assetOnOrder(int $asset): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `asset` FROM `ITSM_DiscardOrder_Asset` WHERE `asset` = ? LIMIT 1');
        $select->bindParam(1, $asset, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param int $asset
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function addAsset(int $id, int $asset): bool
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `ITSM_DiscardOrder_Asset` (`order`, `asset`) VALUES (:order, :asset)');
        $insert->bindParam('order', $id, DatabaseConnection::PARAM_INT);
        $insert->bindParam('asset', $asset, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $handler->close();

        return $insert->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param int $asset
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function removeAsset(int $id, int $asset): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `ITSM_DiscardOrder_Asset` WHERE `asset` = ? AND `order` = ?');
        $delete->bindParam(1, $asset, DatabaseConnection::PARAM_INT);
        $delete->bindParam(2, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function removeAllAssets(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `ITSM_DiscardOrder_Asset` WHERE `order` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $order
     * @return Asset[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectAssetsByOrder(int $order): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `asset` FROM `ITSM_DiscardOrder_Asset` WHERE `order` = ?');
        $select->bindParam(1, $order, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $assets = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $asset)
        {
            try{$assets[] = AssetDatabaseHandler::selectByAssetTag(AssetDatabaseHandler::selectAssetTagById($asset));}
            catch(EntryNotFoundException $e){}
        }

        return $assets;
    }
}
