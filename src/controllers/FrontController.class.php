<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 4:06 PM
 */


namespace controllers;


use business\SecretOperator;
use Config;
use Exception;
use exceptions\ControllerNotFoundException;
use exceptions\DatabaseException;
use exceptions\EntryInUseException;
use exceptions\EntryIsBusyException;
use exceptions\EntryNotFoundException;
use exceptions\JWTException;
use exceptions\RouteException;
use exceptions\SecurityException;
use exceptions\ValidationError;
use factories\ControllerFactory;
use models\HTTPRequest;
use models\HTTPResponse;
use models\Secret;

class FrontController
{
    /**
     * @return Secret
     * @throws SecurityException
     * @throws DatabaseException
     */
    public static function currentSecret(): Secret
    {
        // Check for secret
        if(!isset($_SERVER['HTTP_SECRET']))
            throw new SecurityException(SecurityException::MESSAGES[SecurityException::KEY_NOT_SUPPLIED], SecurityException::KEY_NOT_SUPPLIED);

        try
        {
            return SecretOperator::getSecret($_SERVER['HTTP_SECRET']);
        }
        catch(EntryNotFoundException $e)
        {
            throw new SecurityException(SecurityException::MESSAGES[SecurityException::KEY_NOT_FOUND], SecurityException::KEY_NOT_FOUND, $e);
        }
    }

    public static function processRequest()
    {
        // Set headers
        header('Content-type: application/vnd.api+json');
        header('Access-Control-Allow-Headers: secret, token, jwt, content-type');
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');

        // If all origins are allowed, put them here
        if(Config::OPTIONS['accessControlAllowAllOrigins'])
        {
            header('Access-Control-Allow-Origin: *');
        }

        // Generate final response object
        $response = null;

        try
        {
            $rawMethod = $_SERVER['REQUEST_METHOD'];
            $method = HTTPRequest::GET;

            if($rawMethod === 'POST')
                $method = HTTPRequest::POST;
            else if($rawMethod === 'PUT')
                $method = HTTPRequest::PUT;
            else if($rawMethod === 'DELETE')
                $method = HTTPRequest::DELETE;
            else if($rawMethod === 'OPTIONS')
                self::handleOptionsRequest();

            // Check for valid Secret if not allowing access without one
            if(!Config::OPTIONS['allowAccessWithoutSecret'])
                self::currentSecret();

            // Remove baseURI
            $pos = strpos($_SERVER['REQUEST_URI'], Config::OPTIONS['baseURI']);
            $reqURI = substr_replace($_SERVER['REQUEST_URI'], '', $pos, strlen(Config::OPTIONS['baseURI']));

            // Remove Query Params
            $reqURI = explode('?', $reqURI)[0];

            // Remove trailing slash
            $reqURI = rtrim($reqURI, '/');

            // Break up into parts
            $reqURI = explode('/', $reqURI);

            // Create request
            $request = new HTTPRequest($method, $reqURI, self::getRequestBodyAsArray());

            $response = ControllerFactory::getController($request)->getResponse();

            if($response == NULL)
                throw new RouteException(RouteException::MESSAGES[RouteException::REQUEST_INVALID], RouteException::REQUEST_INVALID);

        }
        catch(JWTException $e)
        {
            $response = new HTTPResponse(HTTPResponse::BAD_REQUEST, array('errors' => array($e->getMessage())));
        }
        catch(RouteException $e)
        {
            $response = new HTTPResponse(HTTPResponse::BAD_REQUEST, array('errors' => array($e->getMessage())));
        }
        catch(EntryNotFoundException $e)
        {
            $response = new HTTPResponse(HTTPResponse::NOT_FOUND, array('errors' => array($e->getMessage())));
        }
        catch(EntryInUseException $e)
        {
            $response = new HTTPResponse(HTTPResponse::CONFLICT, array('errors' => array($e->getMessage())));
        }
        catch(EntryIsBusyException $e)
        {
            $response = new HTTPResponse(HTTPResponse::CONFLICT, array('errors' => array($e->getMessage())));
        }
        catch(SecurityException $e)
        {
            if($e->getCode() == SecurityException::USER_NO_PERMISSION OR $e->getCode() == SecurityException::KEY_NO_PERMISSION)
                $response = new HTTPResponse(HTTPResponse::FORBIDDEN, array('errors' => array($e->getMessage())));
            else
                $response = new HTTPResponse(HTTPResponse::UNAUTHORIZED, array('errors' => array($e->getMessage())));
        }
        catch(ControllerNotFoundException $e)
        {
            $response = new HTTPResponse(HTTPResponse::NOT_FOUND, array('errors' => array($e->getMessage())));
        }
        catch(ValidationError $e)
        {
            $response = new HTTPResponse(HTTPResponse::CONFLICT, array('errors' => $e->getErrors()));
        }
        catch(Exception $e)
        {
            $response = new HTTPResponse(HTTPResponse::INTERNAL_SERVER_ERROR, array('errors' => array($e->getMessage())));
        }

        // Reply to request with response, if it has been set
        if($response !== NULL)
        {
            http_response_code($response->getResponseCode());
            echo json_encode($response->getBody());
            exit;
        }

    }

    /**
     * Converts data sent in a request document to an array
     * @return array
     */
    public static function getRequestBodyAsArray(): array
    {
        $array = json_decode(file_get_contents('php://input'), TRUE);

        if(is_array($array))
            return $array;

        return [];
    }

    /**
     *
     */
    private static function handleOptionsRequest(): void
    {
        http_response_code(200); // This should be OK
        echo '';
        exit;
    }
}
