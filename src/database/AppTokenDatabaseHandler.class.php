<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 11:15 AM
 */


namespace database;


use exceptions\EntryNotFoundException;
use messages\Messages;

class AppTokenDatabaseHandler
{
    /**
     * @param string $token
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectFromToken(string $token): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT id, token, name FROM rest_AppToken WHERE token = ?");
        $select->bindParam(1, $token, DatabaseConnection::PARAM_STR);
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
    public static function doesTokenHaveAccessToRoute(int $tokenID, int $routeID): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT token FROM rest_AppToken_Route WHERE token = :token AND route = :route LIMIT 1");
        $select->bindParam(':token', $tokenID, DatabaseConnection::PARAM_INT);
        $select->bindParam(':route', $routeID, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }
}