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

        $select = $handler->prepare("SELECT id, extension, type, code, name FROM Attribute WHERE id = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("models\Attribute");
    }
}