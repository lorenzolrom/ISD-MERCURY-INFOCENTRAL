<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 9:47 AM
 */


namespace controllers;

use business\BadLoginOperator;
use business\TokenOperator;
use business\UserOperator;
use Config;
use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use exceptions\LDAPException;
use exceptions\SecurityException;
use models\HTTPRequest;
use models\HTTPResponse;
use models\Token;
use utilities\JWTHandler;

/**
 * Class AuthenticateController
 *
 * User authentication
 *
 * @package controllers
 */
class AuthenticateController extends Controller
{
    const LOGIN_FIELDS = array('username', 'password', 'remoteAddr');

    /**
     * @return HTTPResponse|null
     * @throws DatabaseException
     * @throws EntryNotFoundException
     * @throws LDAPException
     * @throws SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        // Allowed authentication methods from config
        $authMethods = Config::OPTIONS['authenticationMethods'];

        $p1 = $this->request->next();
        $p2 = $this->request->next();

        if($this->request->method() == HTTPRequest::POST)
        {
            if(in_array('default', $authMethods))
            {
                if($p1 === 'login' AND $p2 === NULL)
                    return $this->processLoginRequestToken();
            }

            if(in_array('v2', $authMethods))
            {
                if($p1 === 'v2' AND $p2 === 'login')
                    return $this->processLoginRequestJWT();
            }
        }
        else if($this->request->method() == HTTPRequest::PUT)
        {
            if(in_array('default', $authMethods))
            {
                if($p1 === 'logout' AND $p2 === NULL)
                    return $this->processLogoutRequestToken();
            }

            if(in_array('v2', $authMethods))
            {
                if($p1 === 'v2' AND $p2 === 'logout')
                    return $this->processLogoutRequestToken();
            }
        }

        return NULL;
    }

    /**
     * @param array $credentials An array containing 'username' and 'password'
     * @return Token
     * @throws DatabaseException
     * @throws EntryNotFoundException
     * @throws LDAPException
     * @throws SecurityException
     */
    private function loginUser(array $credentials): Token
    {
        if($credentials['username'] === NULL)
            $credentials['username'] = "";
        if($credentials['password'] == NULL)
            $credentials['password'] = "";
        if($credentials['remoteAddr'] == NULL)
            $credentials['remoteAddr'] = $_SERVER['REMOTE_ADDR'];

        return UserOperator::loginUser($credentials['username'], $credentials['password'], $credentials['remoteAddr']);
    }

    /**
     * @return HTTPResponse
     * @throws DatabaseException
     * @throws SecurityException
     * @throws LDAPException
     * @throws EntryNotFoundException
     */
    private function processLoginRequestToken(): HTTPResponse
    {
        $credentials = $this->getFormattedBody(self::LOGIN_FIELDS, TRUE);

        try
        {
            return new HTTPResponse(HTTPResponse::CREATED, array('token' => $this->loginUser($credentials)->getToken()));
        }
        catch(SecurityException $e) // Log bad attempt
        {
            // Log attempt if username not found, user password is incorrect, user is disabled
            if(in_array($e->getCode(), array(SecurityException::USER_NOT_FOUND, SecurityException::USER_PASSWORD_INCORRECT, SecurityException::USER_IS_DISABLED)))
                BadLoginOperator::log((string)$credentials['username'], (string)$credentials['remoteAddr']);
            throw $e;
        }
    }

    /**
     * @return HTTPResponse
     * @throws DatabaseException
     * @throws SecurityException
     */
    private function processLogoutRequestToken(): HTTPResponse
    {
        $token = CurrentUserController::currentToken();
        TokenOperator::expireToken($token);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * Log in the user and provide a JWT
     * @throws DatabaseException
     * @throws EntryNotFoundException
     * @throws LDAPException
     * @throws SecurityException
     * @return HTTPResponse
     */
    private function processLoginRequestJWT(): HTTPResponse
    {
        // Retrieve fields from login
        $credentials = $this->getFormattedBody(self::LOGIN_FIELDS, TRUE);

        // Authenticate user and receive token
        $token = $this->loginUser($credentials);
        $user = UserOperator::getUser($token->getUser());

        // Create header
        $header = json_encode(JWTHandler::HEADER);

        // Create payload
        $payload = json_encode(array(
            'session' => $token->getToken(),
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'permissions' => $user->getPermissions()
        ));

        // Encode header and payload to Base64 string
        $base64UrlHeader =JWTHandler::base64URLEncodeString($header);
        $base64UrlPayload = JWTHandler::base64URLEncodeString($payload);

        // Create signature using 'salt' from Config

        $signature = JWTHandler::generateSignature($base64UrlHeader, $base64UrlPayload);

        // Create JWT
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $signature;

        return new HTTPResponse(HTTPResponse::CREATED, array(
            'access_token' => $jwt,
            'token_type' => 'JWT',
            'expires_in' => 3600
        ));
    }
}
