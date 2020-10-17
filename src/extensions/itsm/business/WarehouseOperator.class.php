<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/13/2019
 * Time: 5:53 PM
 */


namespace extensions\itsm\business;


use business\Operator;
use extensions\itsm\database\AssetDatabaseHandler;
use extensions\itsm\database\PurchaseOrderDatabaseHandler;
use extensions\itsm\database\WarehouseDatabaseHandler;
use exceptions\EntryInUseException;
use exceptions\ValidationException;
use extensions\itsm\models\Warehouse;
use utilities\HistoryRecorder;

class WarehouseOperator extends Operator
{
    /**
     * @param int $id
     * @return Warehouse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getWarehouse(int $id): Warehouse
    {
        return WarehouseDatabaseHandler::selectById($id);
    }

    /**
     * @param string $code
     * @param string $name
     * @param array $closed
     * @return Warehouse[]
     * @throws \exceptions\DatabaseException
     */
    public static function search($code = '%', $name = '%', $closed = array()): array
    {
        return WarehouseDatabaseHandler::select($code, $name, $closed);
    }

    /**
     * @param string|null $code
     * @param string|null $name
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function createWarehouse(?string $code, ?string $name): array
    {
        $errors = self::validateSubmission($code, $name);

        if(!empty($errors))
            return array('errors' => $errors);


        $warehouse = WarehouseDatabaseHandler::insert($code, $name, 0);

        HistoryRecorder::writeHistory('ITSM_Warehouse', HistoryRecorder::CREATE, $warehouse->getId(), $warehouse, array('code' => $code, 'name' => $name));

        return array('id' => $warehouse->getId());
    }

    /**
     * @param Warehouse $warehouse
     * @param string $code
     * @param string $name
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function updateWarehouse(Warehouse $warehouse, ?string $code, ?string $name): array
    {
        $errors = self::validateSubmission($code, $name, $warehouse);

        if(!empty($errors))
            return array('errors' => $errors);

        HistoryRecorder::writeHistory('ITSM_Warehouse', HistoryRecorder::MODIFY, $warehouse->getId(), $warehouse, array('code' => $code, 'name' => $name));

        $warehouse = WarehouseDatabaseHandler::update($warehouse->getId(), $code, $name);

        return array('id' => $warehouse->getId());
    }

    /**
     * @param Warehouse $warehouse
     * @return bool
     * @throws EntryInUseException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function deleteWarehouse(Warehouse $warehouse): bool
    {
        if(AssetDatabaseHandler::areAssetsInWarehouse($warehouse->getId())
            OR PurchaseOrderDatabaseHandler::doPurchaseOrdersReferenceWarehouse($warehouse->getId()))
        {
            throw new EntryInUseException(EntryInUseException::MESSAGES[EntryInUseException::ENTRY_IN_USE], EntryInUseException::ENTRY_IN_USE);
        }

        HistoryRecorder::writeHistory('ITSM_Warehouse', HistoryRecorder::DELETE, $warehouse->getId(), $warehouse);

        return WarehouseDatabaseHandler::delete($warehouse->getId());
    }

    /**
     * @param string $code
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function codeInUse(string $code): bool
    {
        return WarehouseDatabaseHandler::selectIdFromCode($code) !== NULL;
    }

    /**
     * @param int $id
     * @return string
     * @throws \exceptions\DatabaseException
     */
    public static function codeFromId(?int $id): ?string
    {
        if($id === NULL)
            return NULL;

        return WarehouseDatabaseHandler::selectCodeFromId($id);
    }

    /**
     * @param string|null $code
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function idFromCode(?string $code): ?int
    {
        return WarehouseDatabaseHandler::selectIdFromCode((string)$code);
    }

    /**
     * @param string|null $code
     * @param string|null $name
     * @param Warehouse|null $warehouse
     * @return array
     * @throws \exceptions\DatabaseException
     */
    private static function validateSubmission(?string $code, ?string $name, ?Warehouse $warehouse = NULL): array
    {
        $errors = array();

        // code
        if($warehouse === NULL OR $warehouse->getCode() != $code)
        {
            try{Warehouse::validateCode($code);}
            catch(ValidationException $e){$errors[] = $e->getMessage();}
        }

        // name
        try{Warehouse::validateName($name);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        return $errors;
    }
}
