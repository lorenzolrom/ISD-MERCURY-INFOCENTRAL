<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 12:25 PM
 */


namespace extensions\dev\factories;


use extensions\dev\database\QuoteDatabaseHandler;
use extensions\dev\models\Quote;

class QuoteFactory
{
    /**
     * @return Quote[]
     * @throws \exceptions\DatabaseException
     */
    public static function getAllQuotes(): array
    {
        $quotes = array();
        $quotesData = QuoteDatabaseHandler::selectAllQuotes();

        foreach($quotesData as $quoteData)
        {
            $quotes[] = new Quote($quoteData['id'], $quoteData['content']);
        }

        return $quotes;
    }

    /**
     * @param int $id
     * @return Quote
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getQuote(int $id): Quote
    {
        $quoteData = QuoteDatabaseHandler::selectQuoteByID($id);

        return new Quote($quoteData['id'], $quoteData['content']);
    }
}