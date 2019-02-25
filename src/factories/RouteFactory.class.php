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


namespace factories;


use database\RouteDatabaseHandler;
use models\Route;

class RouteFactory
{
    /**
     * @param string $extension
     * @param string $path
     * @return Route
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getRouteByPath(string $extension, string $path): Route
    {
        $routeData = RouteDatabaseHandler::selectRouteByPath($extension, $path);

        return new Route($routeData['id'], $routeData['path'], $routeData['extension'], $routeData['controller']);
    }
}