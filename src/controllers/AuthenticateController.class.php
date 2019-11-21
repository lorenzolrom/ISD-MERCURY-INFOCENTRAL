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
use exceptions\DatabaseException;
use exceptions\SecurityException;
use models\HTTPRequest;
use models\HTTPResponse;

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
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\LDAPException
     * @throws \exceptions\EntryNotFoundException
     */
    public function getResponse(): ?HTTPResponse
    {
        if($this->request->method() == HTTPRequest::POST)
        {
            switch($this->request->next())
            {
                case "login":
                    return $this->loginUser();
            }
        }
        else if($this->request->method() == HTTPRequest::PUT)
        {
            switch($this->request->next())
            {
                case "logout":
                    return $this->logoutUser();
            }
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\LDAPException
     * @throws \exceptions\EntryNotFoundException
     */
    private function loginUser(): HTTPResponse
    {
        $credentials = $this->getFormattedBody(self::LOGIN_FIELDS, TRUE);

        if($credentials['username'] === NULL)
            $credentials['username'] = "";
        if($credentials['password'] == NULL)
            $credentials['password'] = "";
        if($credentials['remoteAddr'] == NULL)
            $credentials['remoteAddr'] = $_SERVER['REMOTE_ADDR'];

        try
        {
            return new HTTPResponse(HTTPResponse::CREATED, array('token' => UserOperator::loginUser($credentials['username'], $credentials['password'], $credentials['remoteAddr'])->getToken()));
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
    private function logoutUser(): HTTPResponse
    {
        $token = CurrentUserController::currentToken();
        TokenOperator::expireToken($token);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

}