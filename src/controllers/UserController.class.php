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
use exceptions\SecurityException;
use models\HTTPRequest;
use models\HTTPResponse;

class UserController extends Controller
{
    const SEARCH_FIELDS = array('username', 'firstName', 'lastName', 'disabled');
    private const FIELDS = array('username', 'firstName', 'lastName', 'email', 'password', 'disabled', 'authType', 'roles');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryInUseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\LDAPException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     * @throws \exceptions\ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission(array('settings', 'tickets-admin'));

        $param = $this->request->next();

        if($this->request->method() == HTTPRequest::GET)
        {
            switch($param)
            {
                case null:
                    return $this->getSearchResult();
                default:
                    CurrentUserController::validatePermission('settings');
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
            CurrentUserController::validatePermission('settings');
            switch($param)
            {
                case 'search':
                    return $this->getSearchResult(TRUE);
                default:
                    return $this->create();
            }
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            CurrentUserController::validatePermission('settings');
            return $this->update($param);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            CurrentUserController::validatePermission('settings');
            return $this->delete($param);
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
        $roles = $user->getRoles();

        $roleList = array();

        foreach($roles as $role)
        {
            $roleList[] = array(
                'id' => $role->getId(),
                'name' => $role->getName()
            );
        }

        $data = array(
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
            'disabled' => $user->getDisabled(),
            'authType' => $user->getAuthType(),
            'roles' => $roleList
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
        $restricted = FALSE;

        try
        {
            CurrentUserController::validatePermission('settings');
        }
        catch(SecurityException $e){$restricted = TRUE;}

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
                'email' => $restricted ? NULL : $user->getEmail(),
                'disabled' => $restricted ? NULL : $user->getDisabled(),
                'authType' => $restricted ? NULL : $user->getAuthType()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $results);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryInUseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function delete(?string $param): HTTPResponse
    {
        $user = UserOperator::getUser((int) $param);

        UserOperator::deleteUser($user);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\LDAPException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function create(): HTTPResponse
    {
        $args = self::getFormattedBody(self::FIELDS);

        return new HTTPResponse(HTTPResponse::CREATED, UserOperator::createUser($args));
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\LDAPException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function update(?string $param): HTTPResponse
    {
        $user = UserOperator::getUser((int) $param);
        $args = self::getFormattedBody(self::FIELDS);
        UserOperator::updateUser($user, $args);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}