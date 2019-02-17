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
}