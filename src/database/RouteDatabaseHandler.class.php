<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 10:58 AM
 */


namespace database;


use exceptions\EntryNotFoundException;
use messages\Messages;

class RouteDatabaseHandler
{
    /**
     * @param string $route
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectRouteByPath(string $route): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT id, path, extension, controller FROM rest_Route WHERE path = ? LIMIT 1");
        $select->bindParam(1, $route, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 1)
            return $select->fetch();

        throw new EntryNotFoundException(Messages::CONTROLLER_NOT_FOUND, EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);
    }
}