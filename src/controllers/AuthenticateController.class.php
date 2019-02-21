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
use factories\TokenFactory;
use messages\Messages;

class AuthenticateController extends Controller
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
        $uriParts = explode("/", $uri);

        if($_SERVER['REQUEST_METHOD'] == "POST")
        {
            if($uriParts[0] == "login")
                return $this->loginUser();
        }
        else if($_SERVER['REQUEST_METHOD'] == "GET")
        {
            if($uriParts[0] == "logout")
                return $this->logoutUser();
            else if($uriParts[0] == "validate")
                return $this->validateToken();
        }

        throw new RouteException(Messages::ROUTE_URI_NOT_FOUND, RouteException::ROUTE_URI_NOT_FOUND);
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
        $submission = FrontController::getDocumentAsArray();
        // Check for loginName and password
        if(!isset($submission['data']['loginName']) OR !isset($submission['data']['password']))
            throw new RouteException(Messages::ROUTE_REQUIRED_PARAMETER_MISSING, RouteException::REQUIRED_PARAMETER_MISSING);

        // Check username
        try
        {
            $user = UserFactory::getFromLoginName($submission['data']['loginName']);
        }
        catch (EntryNotFoundException $e)
        {
            throw new SecurityException(Messages::USER_NOT_FOUND, SecurityException::USER_LOGINNAME_NOT_FOUND);
        }

        // Check password
        $hashedPassword = hash('SHA512', hash('SHA512',$submission['data']['password']));

        if($user->getPassword() != $hashedPassword)
            throw new SecurityException(Messages::USER_PASSWORD_IS_WRONG, SecurityException::USER_PASSWORD_IS_WRONG);

        // Invalidate any existing tokens for this user
        $user->expireAllTokens();

        // Generate new login token
        $token = TokenFactory::getNewToken($user);

        // Return the newly created token
        http_response_code(201);
        return ['data' => [ 'type' => 'UserToken', 'token' => $token->getToken()]];
    }

    /**
     * @return array
     * @throws DatabaseException
     * @throws SecurityException
     * @throws EntryNotFoundException
     */
    private function logoutUser(): array
    {
        FrontController::getCurrentUser()->logout();

        http_response_code(204);
        return[];
    }

    /**
     * @return array
     * @throws DatabaseException
     * @throws EntryNotFoundException
     * @throws SecurityException
     */
    private function validateToken(): array
    {
        FrontController::getCurrentUser();

        http_response_code(204);
        return[];
    }
}