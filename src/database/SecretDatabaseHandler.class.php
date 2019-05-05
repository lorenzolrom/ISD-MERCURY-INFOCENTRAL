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

        $select = $handler->prepare('SELECT `id` FROM `Secret` WHERE `secret` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 1)
            return self::selectById($select->fetchColumn());

        throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);
    }
}