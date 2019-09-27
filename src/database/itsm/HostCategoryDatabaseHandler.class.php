<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/05/2019
 * Time: 9:11 PM
 */


namespace database\itsm;


use business\itsm\HostOperator;
use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use models\itsm\Host;
use models\itsm\HostCategory;

class HostCategoryDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return HostCategory
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): HostCategory
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id`, `name`, `displayed` FROM `ITSM_HostCategory` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('models\itsm\HostCategory');
    }

    /**
     * @param array $displayed
     * @return HostCategory[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(array $displayed = array()): array
    {
        $query = 'SELECT `id` FROM `ITSM_HostCategory`';

        if(!empty($displayed))
            $query .= ' WHERE `displayed` IN (' . self::getBooleanString($displayed) . ')';

        $query .= ' ORDER BY `name` ASC';

        $handler = new DatabaseConnection();

        $select = $handler->prepare($query);
        $select->execute();

        $handler->close();

        $categories = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $categories[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $categories;
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectDisplayed(): array
    {
        return self::select(array(1));
    }

    /**
     * @param string $name
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectIdByName(string $name): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `ITSM_HostCategory` WHERE `name` = ? LIMIT 1');
        $select->bindParam(1, $name, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1 ? $select->fetchColumn() : NULL;
    }

    /**
     * @param string $name
     * @param int $displayed
     * @return HostCategory
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $name, int $displayed): HostCategory
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `ITSM_HostCategory` (`name`, `displayed`) VALUES (:name, :displayed)');
        $insert->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $insert->bindParam('displayed', $displayed, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $name
     * @param int $displayed
     * @return HostCategory
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $name, int $displayed): HostCategory
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `ITSM_HostCategory` SET `name` = :name, `displayed` = :displayed WHERE `id` = :id');
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $update->bindParam('displayed', $displayed, DatabaseConnection::PARAM_INT);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `ITSM_HostCategory` WHERE `id` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param array $hosts
     * @return int
     * @throws DatabaseException
     */
    public static function setHosts(int $id, array $hosts): int
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `ITSM_Host_HostCategory` WHERE `category` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $insert = $handler->prepare('INSERT INTO `ITSM_Host_HostCategory` (`host`, `category`) VALUES (:host, :category)');
        $insert->bindParam('category', $id, DatabaseConnection::PARAM_INT);
        $count = 0;

        foreach($hosts as $host)
        {
            try
            {
                $insert->bindParam('host', $host, DatabaseConnection::PARAM_INT);
                $insert->execute();
                $count++;
            }
            catch(DatabaseException $e){}
        }

        $handler->close();

        return $count;
    }

    /**
     * @param int $id
     * @return Host[]
     * @throws DatabaseException
     */
    public static function getHosts(int $id): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `host` FROM `ITSM_Host_HostCategory` WHERE `category` = ?');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $hosts = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $host)
        {
            try{$hosts[] = HostOperator::getHost($host);}
            catch(EntryNotFoundException $e){}
        }

        return $hosts;
    }
}