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
     */
    public function processURI(string $uri): array
    {
        switch(explode("/", $uri)[0])
        {
            case "login":
                return $this->loginUser();
            case "logout":
                return $this->logoutUser();
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
     * @throws EntryNotFoundException
     */
    private function logoutUser(): array
    {
        // Check for user token
        if(!isset($_SERVER['HTTP_USER_TOKEN']))
            throw new SecurityException(Messages::SECURITY_USERTOKEN_NOT_SUPPLIED, SecurityException::USERTOKEN_NOT_SUPPLIED);

        // Fetch token
        try
        {
            $token = UserTokenFactory::getFromToken($_SERVER['HTTP_USER_TOKEN']);
        }
        catch (EntryNotFoundException $e)
        {
            throw new SecurityException($e->getMessage(), SecurityException::USERTOKEN_NOT_FOUND);
        }

        $user = UserFactory::getFromID($token->getUser());
        $user->logout();

        return ['responseMessage' => Messages::USER_LOGGED_OUT];
    }
}