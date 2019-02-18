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
use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use exceptions\RouteException;
use exceptions\SecurityException;
use exceptions\UserTokenException;
use factories\AppTokenFactory;
use factories\ControllerFactory;
use factories\RouteFactory;
use factories\UserFactory;
use factories\UserTokenFactory;
use messages\Messages;
use models\User;

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

            // Make sure header is set
            if(!isset($_SERVER['HTTP_APPLICATION_TOKEN']))
                throw new SecurityException(Messages::SECURITY_APPTOKEN_NOT_SUPPLIED, SecurityException::APPTOKEN_NOT_SUPPLIED);

            // Create application token object
            $fa_suppliedAppToken = $_SERVER['HTTP_APPLICATION_TOKEN'];
            $fa_appToken = AppTokenFactory::getFromToken($fa_suppliedAppToken);

            // Create Route
            $fa_route = RouteFactory::getRouteByPath($fa_requestedURIParts[1]);

            // Determine if application has permission for route
            if(!$fa_appToken->hasAccessToRoute($fa_route))
                throw new SecurityException(Messages::SECURITY_APPTOKEN_NO_PERMISSION_FOR_ROUTE, SecurityException::APPTOKEN_NO_PERMISSION_FOR_ROUTE);

            // Determine if controller is core or an extension
            if($fa_route->getExtension() === NULL)
                $fa_routeControllerClassname = "/controllers/{$fa_route->getController()}Controller";
            else
                $fa_routeControllerClassname = "/extensions/{$fa_route->getExtension()}/controllers/{$fa_route->getController()}Controller";

            // Check if controller class exists
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

            $fa_finalOutput = $fa_routeController->processURI($fa_routeURI);
        }
        catch(SecurityException $e)
        {
            switch($e->getCode())
            {
                default:
                    http_response_code(401);
                    break;
            }

            $fa_finalOutput['responseMessage'] = $e->getMessage();
        }
        catch(EntryNotFoundException $e)
        {
            switch($e->getCode())
            {
                default:
                    http_response_code(404);
                    break;
            }

            $fa_finalOutput['responseMessage'] = $e->getMessage();
        }
        catch(RouteException $e)
        {
            switch($e->getCode())
            {
                case RouteException::ROUTE_URI_NOT_FOUND:
                    http_response_code(404);
                    break;
                default:
                    http_response_code(400);
                    break;
            }

            $fa_finalOutput['responseMessage'] = $e->getMessage();
        }
        catch(\Exception $e)
        {
            http_response_code(500);
            $fa_finalOutput['responseMessage'] = $e->getMessage();
        }

        return json_encode($fa_finalOutput);
    }

    /**
     * @return User Currently logged in user, if one is present
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\UserTokenException
     */
    public static function getCurrentUser(): User
    {
        if(!isset($_SERVER['HTTP_USER_TOKEN']))
            throw new SecurityException(Messages::SECURITY_NO_AUTHENTICATED_USER, SecurityException::USER_NOT_AUTHENTICATED);

        try
        {
            $token = UserTokenFactory::getFromToken($_SERVER['HTTP_USER_TOKEN']);

            // Has token been marked as expired?
            if($token->getExpired())
                throw new UserTokenException(Messages::USERTOKEN_TOKEN_HAS_EXPIRED, UserTokenException::HAS_EXPIRED);

            // Has expire time passed?
            if(strtotime($token->getExpireTime()) <= strtotime(date('Y-m-d H:i:s')))
            {
                $token->expire();
                throw new UserTokenException(Messages::USERTOKEN_TOKEN_HAS_EXPIRED, UserTokenException::HAS_EXPIRED);
            }
        }
        catch (EntryNotFoundException $e)
        {
            throw new SecurityException($e->getMessage(), SecurityException::USERTOKEN_NOT_FOUND);
        }

        return UserFactory::getFromID($token->getUser());
    }

    /**
     * @param string $permission
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws UserTokenException
     * @throws \exceptions\DatabaseException
     */
    public static function validatePermission(string $permission)
    {
        if(!self::getCurrentUser()->hasPermission($permission))
            throw new SecurityException(Messages::SECURITY_USER_DOES_NOT_HAVE_PERMISSION, SecurityException::USER_NO_PERMISSION);
    }
}