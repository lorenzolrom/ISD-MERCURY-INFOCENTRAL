<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * MERCURY InfoCentral
 *
 * Extension: BookWorm
 * This controller handles the client's request for information about a book
 * through the Google Books API
 *
 * User: lromero
 * Date: 3/09/2019
 * Time: 10:51 AM
 */


namespace extensions\bookworm\controllers;


use controllers\Controller;
use exceptions\RouteException;
use extensions\bookworm\data\GoogleAPIConnection;
use messages\Messages;
use models\Route;

class BookController extends Controller
{
    /**
     * @param string $uri
     * @return array
     * @throws RouteException
     */
    public function processURI(string $uri): array
    {
        $uriParts = explode('/', $uri);

        switch(array_shift($uriParts))
        {
            case "search":
                return $this->search($uriParts);
                break;
            case "volume":
                if(sizeof($uriParts) !== 1)
                    throw new RouteException(Messages::ROUTE_REQUIRED_PARAMETER_MISSING, RouteException::REQUIRED_PARAMETER_MISSING);
                return $this->getVolumeDetails($uriParts[0]);
                break;
            default:
                throw new RouteException(Messages::ROUTE_URI_NOT_FOUND, RouteException::ROUTE_URI_NOT_FOUND);
                break;
        }
    }

    /**
     * @param array $uriParts
     * @return array
     * @throws RouteException
     */
    private function search(array $uriParts): array
    {
        switch(array_shift($uriParts))
        {
            case null:
                throw new RouteException(Messages::ROUTE_REQUIRED_PARAMETER_MISSING, RouteException::REQUIRED_PARAMETER_MISSING);
                break;
            case "title":
                return $this->searchByTitle($uriParts);
                break;
            default:
                throw new RouteException(Messages::ROUTE_URI_NOT_FOUND, RouteException::ROUTE_URI_NOT_FOUND);
                break;
        }
    }

    private function getVolumeDetails(string $id): array
    {
        $results = GoogleAPIConnection::getVolume($id);

        return $results;
    }

    /**
     * @param array $uriParts
     * @return array
     * @throws RouteException
     */
    private function searchByTitle(array $uriParts): array
    {
        if(empty($uriParts))
            throw new RouteException(Messages::ROUTE_REQUIRED_PARAMETER_MISSING, RouteException::REQUIRED_PARAMETER_MISSING);

        $results = GoogleAPIConnection::searchTitle(array_shift($uriParts));

        if(isset($results['items']))
            return $results['items'];

        return array();
    }
}