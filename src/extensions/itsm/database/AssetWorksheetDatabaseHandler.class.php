<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 5/12/2019
 * Time: 12:04 PM
 */


namespace extensions\itsm\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;

class AssetWorksheetDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function addToWorksheet(int $id): int
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `ITSM_Asset_Worksheet` (`asset`) VALUES (?)');
        $insert->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $handler->close();

        return 1;
    }

    /**
     * @param array $assets
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function bulkAddToWorksheet(array $assets): int
    {
        $count = 0;
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `ITSM_Asset_Worksheet` (`asset`) VALUES (?)');

        foreach($assets as $asset)
        {
            $insert->bindParam(1, $asset, DatabaseConnection::PARAM_INT);
            $insert->execute();
            $count++;
        }

        $handler->close();

        return $count;
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function removeFromWorksheet(int $id): bool
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('DELETE FROM `ITSM_Asset_Worksheet` WHERE `asset` = ?');
        $insert->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $handler->close();

        return TRUE;
    }

    /**
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function clearWorksheet(): int
    {
        $handler = new DatabaseConnection();

        /** @noinspection SqlWithoutWhere */
        $delete = $handler->prepare('DELETE FROM `ITSM_Asset_Worksheet`');
        $delete->execute();

        $handler->close();

        return $delete->getRowCount();
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function getWorksheetAssets(): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `asset` FROM `ITSM_Asset_Worksheet`');
        $select->execute();

        $handler->close();

        $assets = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$assets[] = AssetDatabaseHandler::selectByAssetTag(AssetDatabaseHandler::selectAssetTagById($id));}
            catch(EntryNotFoundException $e){}
        }

        return $assets;
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function isAssetInWorksheet(int $id): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `asset` FROM `ITSM_Asset_Worksheet` WHERE `asset` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function getWorksheetCount(): int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `asset` FROM `ITSM_Asset_Worksheet`');
        $select->execute();

        $handler->close();

        return $select->getRowCount();
    }
}
