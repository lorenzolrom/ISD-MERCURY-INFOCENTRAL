<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * MERCURY InfoCentral
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 11:15 AM
 */


namespace database;


use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use messages\Messages;

class SecretDatabaseHandler
{
    /**
     * @param string $secret
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectFromSecret(string $secret): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT id, secret, name, exempt FROM \"fa_Secret\" WHERE secret = ?");
        $select->bindParam(1, $secret, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 1)
            return $select->fetch();

        throw new EntryNotFoundException(Messages::SECURITY_APPTOKEN_NOT_FOUND, EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);
    }

    /**
     * @param int $tokenID
     * @param int $routeID
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function doesSecretHaveAccessToRoute(int $tokenID, int $routeID): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT secret FROM \"fa_Secret_Route\" WHERE secret = :token AND route = :route LIMIT 1");
        $select->bindParam(':token', $tokenID, DatabaseConnection::PARAM_INT);
        $select->bindParam(':route', $routeID, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @return array
     * @throws DatabaseException
     */
    public static function getSecretPermissionCodes(int $id): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT permission FROM \"fa_Secret_Permission\" WHERE secret = ?");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0);
    }
}