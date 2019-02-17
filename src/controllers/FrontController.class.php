<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 10:44 AM
 */


namespace controllers;


use exceptions\ControllerException;
use exceptions\RouteException;
use exceptions\SecurityException;
use factories\AppTokenFactory;
use factories\ControllerFactory;
use factories\RouteFactory;
use http\Message;
use messages\Messages;

class FrontController
{
    public static function processRequest(): string
    {
        // Final array to be output as JSON
        $fa_finalOutput = [];

        try
        {
            // Retrieve request URL and Path
            if(isset($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] == 'on')
                $fa_requestedURL = "https";
            else
                $fa_requestedURL = "http";

            $fa_requestedURL .= "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            // Check if a route was supplied
            if(!isset(explode(FA_APP_URL . FA_APP_URI, $fa_requestedURL)[1]))
                throw new RouteException(Messages::ROUTE_NOT_SUPPLIED, RouteException::ROUTE_NOT_SUPPLIED);

            $fa_requestedURI = "/" . explode(FA_APP_URL . FA_APP_URI, $fa_requestedURL)[1]; // Get portion of URL after application
            $fa_requestedURIParts = explode('/', explode('?', $fa_requestedURI)[0]); // Break URI into pieces (ignore GET variables)

            // Retrieve token
            $fa_requestHeaders = getallheaders();

            // Make sure header is set
            if(!isset($fa_requestHeaders['Application-Token']))
                throw new SecurityException(Messages::SECURITY_APPTOKEN_NOT_SUPPLIED, SecurityException::APPTOKEN_NOT_SUPPLIED);

            // Create application token object
            $fa_suppliedAppToken = $fa_requestHeaders['Application-Token'];
            $fa_appToken = AppTokenFactory::getFromToken($fa_suppliedAppToken);

            // Create Route
            $fa_route = RouteFactory::getRouteByPath($fa_requestedURIParts[1]);

            // Determine if application has permission for route
            if(!$fa_appToken->hasAccessToRoute($fa_route))
                throw new SecurityException(Messages::SECURITY_APPTOKEN_NO_PERMISSION_FOR_ROUTE, SecurityException::APPTOKEN_NO_PERMISSION_FOR_ROUTE);

            // Check if controller class exists for route
            $fa_routeControllerClassname = "/extensions/{$fa_route->getExtension()}/controllers/{$fa_route->getController()}Controller";
            $fa_routeControllerPath = dirname(__FILE__) . "/..$fa_routeControllerClassname.class.php";

            if(!is_file($fa_routeControllerPath))
                throw new ControllerException(Messages::CONTROLLER_NOT_FOUND, ControllerException::CONTROLLER_NOT_FOUND);
            else
            {
                /** @noinspection PhpIncludeInspection */
                require_once($fa_routeControllerPath);
            }

            // Load controller class
            $fa_routeController = ControllerFactory::getController(str_replace("/", "\\", $fa_routeControllerClassname));

            // Request results of route URI
            $fa_routeURI = "";

            for($i = 2; $i < sizeof($fa_requestedURIParts); $i++)
            {
                $fa_routeURI .= $fa_requestedURIParts[$i] . "/";
            }

            $fa_routeURI = rtrim($fa_routeURI, "/");

            $fa_finalOutput['responseType'] = "OK";
            $fa_finalOutput = array_merge($fa_finalOutput, $fa_routeController->processURI($fa_routeURI));
        }
        catch(\Exception $e)
        {
            $fa_finalOutput['responseType'] = "ERROR";
            $fa_finalOutput['responseCode'] = $e->getCode();
            $fa_finalOutput['responseMessage'] = $e->getMessage();
        }

        return json_encode($fa_finalOutput);
    }
}