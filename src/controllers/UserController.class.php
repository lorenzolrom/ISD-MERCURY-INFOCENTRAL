<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/06/2019
 * Time: 3:54 PM
 */


namespace controllers;


use business\UserOperator;
use models\HTTPRequest;
use models\HTTPResponse;

class UserController extends Controller
{
    const SEARCH_FIELDS = array('username', 'firstName', 'lastName', 'disabled');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('settings');

        $param = $this->request->next();

        if($this->request->method() == HTTPRequest::GET)
        {
            switch($param)
            {
                case null:
                    return $this->getSearchResult();
                default:
                    switch($this->request->next())
                    {
                        case "roles":
                            return $this->getRolesById($param);
                        case "permissions":
                            return $this->getPermissionsById($param);
                    }

                    return $this->getById($param);
            }
        }
        else if($this->request->method() == HTTPRequest::POST)
        {
            switch($param)
            {
                case 'search':
                    return $this->getSearchResult(TRUE);
            }
        }

        return NULL;
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getById(?string $param): HTTPResponse
    {
        $user = UserOperator::getUser((int)$param);

        $data = array(
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
            'disabled' => $user->getDisabled(),
            'authType' => $user->getAuthType()
        );

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getRolesById(?string $param): HTTPResponse
    {
        $user = UserOperator::getUser((int) $param);

        $data = array();

        foreach($user->getRoles() as $role)
        {
            $data[] = array(
                'id' => $role->getId(),
                'name' => $role->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getPermissionsById(?string $param): HTTPResponse
    {
        $user = UserOperator::getUser((int) $param);

        $data = array();

        foreach($user->getPermissions() as $permission)
        {
            $data[] = $permission;
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param bool $search
     * @param bool $strict
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getSearchResult(bool $search = FALSE, bool $strict = FALSE): HTTPResponse
    {
        if($search)
        {
            $args = $this->getFormattedBody(self::SEARCH_FIELDS, $strict);

            $users = UserOperator::search($args['username'], $args['firstName'], $args['lastName'], $args['disabled']);
        }
        else
            $users = UserOperator::search();

        $results = array();

        foreach($users as $user)
        {
            $results[] = array(
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'disabled' => $user->getDisabled(),
                'authType' => $user->getAuthType()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $results);
    }
}