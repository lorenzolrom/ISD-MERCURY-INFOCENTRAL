<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 5:51 PM
 */


namespace database;


use exceptions\DatabaseException;
use exceptions\EntryInUseException;
use exceptions\EntryNotFoundException;
use models\Attribute;

class AttributeDatabaseHandler
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

        $select = $handler->prepare("SELECT `id`, `extension`, `type`, `code`, `name` FROM `Attribute` WHERE `id` = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("models\Attribute");
    }

    /**
     * Used to make sure the Attribute ID belongs to the extension/type specified
     *
     * @param string $extension
     * @param string $type
     * @param int $id
     * @return Attribute
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectByExtensionTypeId(string $extension, string $type, int $id): Attribute
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id`, `extension`, `type`, `code`, `name` FROM `Attribute` WHERE `id` = :id AND `extension` LIKE :extension AND `type` LIKE :type LIMIT 1");
        $select->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $select->bindParam('extension', $extension, DatabaseConnection::PARAM_STR);
        $select->bindParam('type', $type, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("models\Attribute");
    }

    /**
     * @param string $extension
     * @param string $type
     * @return Attribute[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $extension = '%', string $type = '%'): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `Attribute` WHERE `extension` LIKE :extension AND `type` LIKE :type ORDER BY `name` ASC");
        $select->bindParam('extension', $extension, DatabaseConnection::PARAM_STR);
        $select->bindParam('type', $type, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $attributes = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $attributes[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $attributes;
    }

    /**
     * @param int $id
     * @return string
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectNameById(int $id): string
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `name` FROM `Attribute` WHERE `id` = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchColumn();
    }

    /**
     * @param string $extension
     * @param string $type
     * @param string $code
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function idFromCode(string $extension, string $type, string $code): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `Attribute` WHERE `extension` = :extension AND `type` = :type AND `code` = :code LIMIT 1");
        $select->bindParam('extension', $extension, DatabaseConnection::PARAM_STR);
        $select->bindParam('type', $type, DatabaseConnection::PARAM_STR);
        $select->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 1)
            return $select->fetchColumn();

        return NULL;
    }

    /**
     * @param int $id
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function codeFromId(int $id): ?string
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `code` FROM `Attribute` WHERE `id` = :id LIMIT 1");
        $select->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 1)
            return $select->fetchColumn();

        return NULL;
    }

    /**
     * @param string $extension
     * @param string $type
     * @param string $code
     * @param string $name
     * @return Attribute
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $extension, string $type, string $code, string $name): Attribute
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare("INSERT INTO `Attribute` (`extension`, `type`, `code`, `name`) VALUES (:extension, :type, :code, :name)");
        $insert->bindParam('extension', $extension, DatabaseConnection::PARAM_STR);
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

        $update = $handler->prepare("UPDATE `Attribute` SET `code` = :code, `name` = :name WHERE `id` = :id");
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
     * @throws EntryInUseException
     */
    public static function delete(int $id): bool
    {
        try
        {
            $handler = new DatabaseConnection();

            $delete = $handler->prepare("DELETE FROM `Attribute` WHERE `id` = ?");
            $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
            $delete->execute();

            $handler->close();

            return $delete->getRowCount() === 1;
        }
        catch(DatabaseException $e)
        {
            throw new EntryInUseException(EntryInUseException::MESSAGES[EntryInUseException::ENTRY_IN_USE], EntryInUseException::ENTRY_IN_USE);
        }
    }

    /**
     * @param string $extension
     * @param string $type
     * @param string $code
     * @return bool
     * @throws DatabaseException
     */
    public static function isCodeValid(string $extension, string $type, string $code): bool
    {
        $handler = new DatabaseConnection();

        $check = $handler->prepare('SELECT `id` FROM `Attribute` WHERE `extension` = :extension AND `type` = :type AND `code` = :code LIMIT 1');
        $check->bindParam('extension', $extension, DatabaseConnection::PARAM_STR);
        $check->bindParam('type', $type, DatabaseConnection::PARAM_STR);
        $check->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $check->execute();

        $handler->close();

        return $check->getRowCount() === 1;
    }
}