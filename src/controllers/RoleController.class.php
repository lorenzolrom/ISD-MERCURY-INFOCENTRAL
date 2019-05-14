<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 11:00 AM
 */


namespace controllers;


use business\RoleOperator;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class RoleController extends Controller
{
    private const FIELDS = array('name', 'permissions');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
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
                    if($this->request->next() == "permissions")
                        return $this->getPermissionsById($param);
                    return $this->getById($param);
            }
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            switch($param)
            {
                case 'search':
                    return $this->getSearchResult(TRUE);
                default:
                    return $this->createRole();
            }
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            return $this->updateRole($param);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            return $this->deleteRole($param);
        }

        return NULL;
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getById(?string $param): HTTPResponse
    {
        $role = RoleOperator::getRole((int)$param);

        $data = array(
            'id' => $role->getId(),
            'name' => $role->getName()
        );

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param bool $search
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getSearchResult(bool $search = FALSE): HTTPResponse
    {
        $data = array();

        if($search)
        {
            $args = self::getFormattedBody(self::FIELDS, FALSE);
            $roles = RoleOperator::search($args['name']);
        }
        else
        {
            $roles = RoleOperator::search();
        }

        foreach($roles as $role)
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
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getPermissionsById(?string $param):HTTPResponse
    {
        $role = RoleOperator::getRole((int)$param);
        $data = array();

        foreach($role->getPermissions() as $permission)
        {
            $data[] = array(
                'code' => $permission->getCode()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function createRole(): HTTPResponse
    {
        return new HTTPResponse(HTTPResponse::CREATED, RoleOperator::createRole(self::getFormattedBody(self::FIELDS)));
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function updateRole(?string $param): HTTPResponse
    {
        $role = RoleOperator::getRole((int) $param);

        RoleOperator::updateRole($role, self::getFormattedBody(self::FIELDS));
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function deleteRole(?string $param): HTTPResponse
    {
        $role = RoleOperator::getRole((int) $param);

        RoleOperator::deleteRole($role);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}