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

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
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
                    if($this->request->next() == "permissions")
                        return $this->getPermissionsById($param);
                    return $this->getById($param);
            }
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
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getSearchResult(): HTTPResponse
    {
        $data = array();

        foreach(RoleOperator::search() as $role)
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
}