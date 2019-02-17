<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 11:51 AM
 */


namespace extensions\dev\controllers;


use controllers\Controller;
use exceptions\RouteException;
use extensions\dev\factories\QuoteFactory;
use messages\Messages;

class TestController extends Controller
{

    /**
     * @param string $uri
     * @return array
     * @throws RouteException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public function processURI(string $uri): array
    {
        switch(explode("/", $uri)[0])
        {
            case "quotes":
                return $this->getQuoteList();
            case "quote":
                return $this->getQuote($uri);
            default:
                throw new RouteException(Messages::ROUTE_URI_NOT_FOUND, RouteException::ROUTE_URI_NOT_FOUND);
        }
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     */
    private function getQuoteList(): array
    {
        $data = array();

        foreach(QuoteFactory::getAllQuotes() as $quote)
        {
            $data['quotes'][] = ['id' => $quote->getId(), 'content' => $quote->getContent()];
        }

        return $data;
    }

    /**
     * @param string $uri
     * @return array
     * @throws RouteException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getQuote(string $uri): array
    {
        $requestParts = explode("/", $uri);

        if(empty($requestParts) OR !isset($requestParts[1]))
            throw new RouteException(Messages::ROUTE_REQUIRED_PARAMETER_MISSING, RouteException::REQUIRED_PARAMETER_MISSING);

        $quote = QuoteFactory::getQuote((int)$requestParts[1]);
        return ['content' => $quote->getContent()];
    }
}