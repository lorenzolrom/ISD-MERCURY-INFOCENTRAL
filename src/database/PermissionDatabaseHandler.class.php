<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * MERCURY InfoCentral
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 6:05 PM
 */


namespace database;


use exceptions\EntryNotFoundException;
use messages\Messages;

class PermissionDatabaseHandler
{
    /**
     * @param string $code
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectFromCode(string $code): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT \"code\", \"displayName\", \"description\" FROM \"fa_Permission\" WHERE \"code\" = ? LIMIT 1");
        $select->bindParam(1, $code, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 1)
            return $select->fetch();

        throw new EntryNotFoundException(Messages::PERMISSION_NOT_FOUND, EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);
    }
}