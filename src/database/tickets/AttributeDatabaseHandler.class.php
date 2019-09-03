<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/13/2019
 * Time: 5:59 PM
 */


namespace database\tickets;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\DatabaseException;
use exceptions\EntryInUseException;
use exceptions\EntryNotFoundException;
use models\tickets\Attribute;

class AttributeDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Attribute
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Attribute
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id`, `workspace`, `type`, `code`, `name` FROM `Tickets_Attribute` WHERE `id` = :id LIMIT 1');
        $select->bindParam('id', $id, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('models\tickets\Attribute');
    }

    /**
     * @param int $workspace
     * @param string $type
     * @return array
     * @throws DatabaseException
     */
    public static function selectByType(int $workspace, string $type): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Tickets_Attribute` WHERE `workspace` = :workspace AND `type` = :type ORDER BY `name` ASC');
        $select->bindParam('workspace', $workspace, DatabaseConnection::PARAM_INT);
        $select->bindParam('type', $type, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $attributes = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$attributes[] = self::selectById($id);}
            catch(EntryNotFoundException $e){}
        }

        return $attributes;
    }

    /**
     * @param int $workspace
     * @param string $type
     * @param string $code
     * @param string $name
     * @return Attribute
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $workspace, string $type, string $code, string $name): Attribute
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `Tickets_Attribute` (`workspace`, `type`, `code`, `name`) 
              VALUES (:workspace, :type, :code, :name)');
        $insert->bindParam('workspace', $workspace, DatabaseConnection::PARAM_STR);
        $insert->bindParam('type', $type, DatabaseConnection::PARAM_STR);
        $insert->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $insert->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $code
     * @param string $name
     * @return Attribute
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $code, string $name): Attribute
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `Tickets_Attribute` SET `code` = :code, `name` = :name WHERE `id` = :id');
        $update->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $update->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws EntryInUseException
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `Tickets_Attribute` WHERE `id` = :id');
        $delete->bindParam('id', $id, DatabaseConnection::PARAM_INT);

        try{$delete->execute();}
        catch(DatabaseException $e){throw new EntryInUseException(EntryInUseException::MESSAGES[EntryInUseException::ENTRY_IN_USE], EntryInUseException::ENTRY_IN_USE, $e);}

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @return string|null
     * @throws DatabaseException
     */
    public static function selectNameById(int $id): ?string
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `name` FROM `Tickets_Attribute` WHERE `id` = :id LIMIT 1');
        $select->bindParam('id', $id, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1 ? $select->fetchColumn() : NULL;
    }

    /**
     * @param int $id
     * @return string|null
     * @throws DatabaseException
     */
    public static function selectCodeById(int $id): ?string
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `code` FROM `Tickets_Attribute` WHERE `id` = :id LIMIT 1');
        $select->bindParam('id', $id, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1 ? $select->fetchColumn() : NULL;
    }

    /**
     * @param int $workspace
     * @param string $type
     * @param string $code
     * @return int|null
     * @throws DatabaseException
     */
    public static function selectIdByCode(int $workspace, string $type, string $code): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Tickets_Attribute` WHERE `workspace` = :workspace AND `type` = :type AND `code` = :code LIMIT 1');
        $select->bindParam('workspace', $workspace, DatabaseConnection::PARAM_INT);
        $select->bindParam('type', $type, DatabaseConnection::PARAM_STR);
        $select->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1 ? $select->fetchColumn() : NULL;
    }

    /**
     * @param int $id
     * @return bool
     * @throws DatabaseException
     */
    public static function attributeInUse(int $id): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Tickets_Ticket` WHERE `status` = :attr OR `category` = :attr OR `severity` = :attr OR `type` LIKE :attr OR `closureCode` LIKE :attr LIMIT 1');
        $select->bindParam('attr', $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }
}