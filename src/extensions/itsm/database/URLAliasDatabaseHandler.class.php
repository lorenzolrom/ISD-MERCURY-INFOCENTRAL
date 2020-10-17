<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/02/2019
 * Time: 8:50 PM
 */


namespace extensions\itsm\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\itsm\models\URLAlias;

class URLAliasDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return URLAlias
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): URLAlias
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id`, `alias`, `destination`, `disabled` FROM `NIS_URLAlias` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('extensions\itsm\models\URLAlias');
    }

    /**
     * @param string $alias
     * @param string $destination
     * @param array $disabled
     * @return URLAlias[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $alias = '%', string $destination = '%', $disabled = array()): array
    {
        $query = 'SELECT `id` FROM `NIS_URLAlias` WHERE `alias` LIKE :alias AND `destination` LIKE :destination';

        if(is_array($disabled) AND !empty($disabled))
            $query .= 'AND `disabled` IN (' . self::getBooleanString($disabled) . ')';

        $handler = new DatabaseConnection();

        $select = $handler->prepare($query);
        $select->bindParam('alias', $alias, DatabaseConnection::PARAM_STR);
        $select->bindParam('destination', $destination, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $aliases = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $aliases[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $aliases;
    }

    /**
     * @param string $alias
     * @param string $destination
     * @param int $disabled
     * @return URLAlias
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $alias, string $destination, int $disabled): URLAlias
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `NIS_URLAlias` (alias, destination, disabled) VALUES (:alias, :destination, :disabled)');
        $insert->bindParam('alias', $alias, DatabaseConnection::PARAM_STR);
        $insert->bindParam('destination', $destination, DatabaseConnection::PARAM_STR);
        $insert->bindParam('disabled', $disabled, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $alias
     * @param string $destination
     * @param int $disabled
     * @return URLAlias
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $alias, string $destination, int $disabled): URLAlias
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `NIS_URLAlias` SET `alias` = :alias, `destination` = :destination, `disabled` = :disabled WHERE `id` = :id');
        $update->bindParam('alias', $alias, DatabaseConnection::PARAM_STR);
        $update->bindParam('destination', $destination, DatabaseConnection::PARAM_STR);
        $update->bindParam('disabled', $disabled, DatabaseConnection::PARAM_INT);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
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

        $delete = $handler->prepare('DELETE FROM `NIS_URLAlias` WHERE `id` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param string $alias
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectIdFromAlias(string $alias): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `NIS_URLAlias` WHERE `alias` = ? LIMIT 1');
        $select->bindParam(1, $alias, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1 ? $select->fetchColumn() : NULL;
    }
}
