<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 4:21 PM
 */


namespace database;


use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use models\Secret;

class SecretDatabaseHandler extends DatabaseHandler
{
    /**
     * @param string $secret
     * @return Secret
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     */
    public static function selectBySecret(string $secret): Secret
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id`, `secret`, `name` FROM `Secret` WHERE `secret` = ? LIMIT 1");
        $select->bindParam(1, $secret, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 1)
            return $select->fetchObject("models\Secret");

        throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);
    }

    /**
     * @param int $id
     * @return Secret
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Secret
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `secret` FROM `Secret` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 1)
            return self::selectBySecret($select->fetchColumn());

        throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);
    }

    /**
     * @return Secret[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `secret` FROM `Secret`');
        $select->execute();

        $handler->close();

        $secrets = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $secret)
        {
            try{$secrets[] = self::selectBySecret($secret);}
            catch(EntryNotFoundException $e){}
        }

        return $secrets;
    }

    /**
     * @param string $secret
     * @param string $name
     * @return Secret
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $secret, string $name): Secret
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `Secret` (`secret`, `name`) VALUES (:secret, :name)');
        $insert->bindParam('secret', $secret, DatabaseConnection::PARAM_STR);
        $insert->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $handler->close();

        return self::selectBySecret($secret);
    }

    /**
     * @param int $id
     * @param string $name
     * @return Secret
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $name): Secret
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `Secret` SET `name` = :name WHERE `id` = :id');
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
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `Secret` WHERE `id` = :id');
        $delete->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param string $secret
     * @param array $permissions
     * @return int
     * @throws DatabaseException
     */
    public static function setPermissions(string $secret, array $permissions): int
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `Secret_Permission` WHERE `secret` = ?');
        $delete->bindParam(1, $secret, DatabaseConnection::PARAM_STR);
        $delete->execute();

        $insert = $handler->prepare('INSERT INTO `Secret_Permission` (`secret`, `permission`) VALUES (:secret, :permission)');
        $insert->bindParam('secret', $secret, DatabaseConnection::PARAM_STR);
        $count = 0;

        foreach($permissions as $permission)
        {
            try
            {
                $insert->bindParam('permission', $permission, DatabaseConnection::PARAM_STR);
                $insert->execute();
                $count++;
            }
            catch(DatabaseException $e){}
        }

        $handler->close();

        return $count;
    }

    /**
     * @param string $name
     * @return int|null
     * @throws DatabaseException
     */
    public static function selectIdFromName(string $name): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Secret` WHERE `name` = ? LIMIT 1');
        $select->bindParam(1, $name, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1 ? $select->fetchColumn() : NULL;
    }
}