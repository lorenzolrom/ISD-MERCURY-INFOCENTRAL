<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 3:23 PM
 */


namespace controllers;


use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use exceptions\RouteException;
use exceptions\SecurityException;
use \exceptions\UserTokenException;
use factories\UserFactory;
use factories\UserTokenFactory;
use messages\Messages;

class AuthenticationController extends Controller
{

    /**
     * @param string $uri
     * @return array
     * @throws DatabaseException
     * @throws EntryNotFoundException
     * @throws RouteException
     * @throws SecurityException
     * @throws UserTokenException
     */
    public function processURI(string $uri): array
    {
        switch(explode("/", $uri)[0])
        {
            case "login":
                return $this->loginUser();
            case "logout":
                return $this->logoutUser();
            case "validate":
                return $this->validateToken();
            default:
                throw new RouteException(Messages::ROUTE_URI_NOT_FOUND, RouteException::ROUTE_URI_NOT_FOUND);
        }
    }

    /**
     * @return array
     * @throws DatabaseException
     * @throws RouteException
     * @throws SecurityException
     * @throws EntryNotFoundException
     */
    private function loginUser(): array
    {
        // Check for loginName and password
        if(!isset($_POST['loginName']) OR !isset($_POST['password']))
            throw new RouteException(Messages::ROUTE_REQUIRED_PARAMETER_MISSING, RouteException::REQUIRED_PARAMETER_MISSING);

        // Check username
        try
        {
            $user = UserFactory::getFromLoginName($_POST['loginName']);
        }
        catch (EntryNotFoundException $e)
        {
            throw new SecurityException(Messages::USER_NOT_FOUND, SecurityException::USER_LOGINNAME_NOT_FOUND);
        }

        // Check password
        $hashedPassword = hash('SHA512', hash('SHA512',$_POST['password']));

        if($user->getPassword() != $hashedPassword)
            throw new SecurityException(Messages::USER_PASSWORD_IS_WRONG, SecurityException::USER_PASSWORD_IS_WRONG);

        // Invalidate any existing tokens for this user
        $user->expireAllTokens();

        // Generate new login token
        $token = UserTokenFactory::getNewToken($user);

        // Return the newly created token
        return ['userToken' => $token->getToken()];
    }

    /**
     * @return array
     * @throws DatabaseException
     * @throws SecurityException
     * @throws \exceptions\UserTokenException
     * @throws RouteException
     */
    private function logoutUser(): array
    {
        // Check for user token
        if(!isset($_SERVER['HTTP_USER_TOKEN']))
            throw new RouteException(Messages::ROUTE_REQUIRED_PARAMETER_MISSING, RouteException::REQUIRED_PARAMETER_MISSING);

        // Fetch token
        try
        {
            $token = UserTokenFactory::getFromToken($_SERVER['HTTP_USER_TOKEN']);
        }
        catch (EntryNotFoundException $e)
        {
            throw new SecurityException($e->getMessage(), SecurityException::USERTOKEN_NOT_FOUND);
        }

        $token->expire();

        return ['responseMessage' => Messages::USER_LOGGED_OUT];
    }

    /**
     * @return array
     * @throws DatabaseException
     * @throws RouteException
     * @throws UserTokenException
     */
    private function validateToken(): array
    {
        // Check for user token
        if(!isset($_SERVER['HTTP_USER_TOKEN']))
            throw new RouteException(Messages::ROUTE_REQUIRED_PARAMETER_MISSING, RouteException::REQUIRED_PARAMETER_MISSING);

        // Fetch token
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
        catch(EntryNotFoundException $e)
        {
            throw new RouteException(Messages::ROUTE_REQUIRED_PARAMETER_IS_INVALID, RouteException::REQUIRED_PARAMETER_IS_INVALID);
        }

        return ['responseMessage' => 'Token Is Valid'];
    }
}