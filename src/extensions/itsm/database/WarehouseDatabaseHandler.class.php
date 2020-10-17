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
 * Time: 5:18 PM
 */


namespace extensions\itsm\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\itsm\models\Warehouse;

class WarehouseDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Warehouse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Warehouse
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id`, `code`, `name`, `closed` FROM `ITSM_Warehouse` WHERE `id` = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("extensions\itsm\models\Warehouse");
    }

    /**
     * @param string $code
     * @param string $name
     * @param array $closed
     * @return Warehouse[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $code = '%', string $name = '%', $closed = array()): array
    {
        $query = "SELECT `id` FROM `ITSM_Warehouse` WHERE `code` LIKE :code AND `name` LIKE :name";

        if(is_array($closed) AND !empty($closed))
            $query .= " AND `closed` IN (" . self::getBooleanString($closed) . ")";

        $query .= " ORDER BY `code` ASC";

        $handler = new DatabaseConnection();

        $select = $handler->prepare($query);
        $select->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $select->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $warehouses = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $warehouses[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $warehouses;
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare("DELETE FROM `ITSM_Warehouse` WHERE `id` = ?");
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param string $code
     * @param string $name
     * @param int $closed
     * @return Warehouse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $code, string $name, int $closed): Warehouse
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare("INSERT INTO `ITSM_Warehouse` (code, name, closed) VALUES (:code, :name, :closed)");
        $insert->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $insert->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $insert->bindParam('closed', $closed, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $code
     * @param string $name
     * @return Warehouse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $code, string $name): Warehouse
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare("UPDATE `ITSM_Warehouse` SET `code` = :code, `name` = :name WHERE `id` = :id");
        $update->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $update->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param string $code
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectIdFromCode(string $code): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `ITSM_Warehouse` WHERE `code` = ? LIMIT 1");
        $select->bindParam(1, $code, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            return NULL;

        return $select->fetchColumn();
    }

    /**
     * @param int $id
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectCodeFromId(int $id): ?string
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `code` FROM `ITSM_Warehouse` WHERE `id` = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            return NULL;

        return $select->fetchColumn();
    }
}
