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
    const LOGIN_FIELDS = array('username', 'password');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\LDAPException
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
     */
    private function loginUser(): HTTPResponse
    {
        $credentials = $this->getFormattedBody(self::LOGIN_FIELDS, TRUE);

        if($credentials['username'] === NULL)
            $credentials['username'] = "";
        if($credentials['password'] == NULL)
            $credentials['password'] = "";

        return new HTTPResponse(HTTPResponse::CREATED, array('token' => UserOperator::loginUser($credentials['username'], $credentials['password'])->getToken()));
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