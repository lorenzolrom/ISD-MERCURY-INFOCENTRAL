<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 12:22 PM
 */


namespace extensions\dev\database;


use database\DatabaseConnection;
use exceptions\EntryNotFoundException;
use extensions\dev\Messages;

class QuoteDatabaseHandler
{
    /**
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectAllQuotes(): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT id, content FROM test_Quote");
        $select->execute();

        $handler->close();

        return $select->fetchAll();
    }

    /**
     * @param int $id
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectQuoteByID(int $id): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT id, content FROM test_Quote WHERE id = :id LIMIT 1");
        $select->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 1)
            return $select->fetch();

        throw new EntryNotFoundException(Messages::QUOTE_NOT_FOUND, EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);
    }
}