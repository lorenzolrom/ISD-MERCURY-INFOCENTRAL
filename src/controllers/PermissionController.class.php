<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 1:11 PM
 */


namespace controllers;


use business\PermissionOperator;
use models\HTTPRequest;
use models\HTTPResponse;

class PermissionController extends Controller
{
    private const FIELDS = array('permission');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('settings');

        if($this->request->method() == HTTPRequest::GET)
        {
            switch($this->request->next())
            {
                case null:
                    return $this->getList();
            }
        }
        else if($this->request->method() === HTTPRequest::POST AND $this->request->next() == 'audit')
        {
            return $this->getUsersWithPermission();
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getList(): HTTPResponse
    {
        $data = array();

        foreach(PermissionOperator::search() as $permission)
        {
            $data[] = array(
                'code' => $permission->getCode()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getUsersWithPermission(): HTTPResponse
    {
        $args = self::getFormattedBody(self::FIELDS);

        $users = PermissionOperator::getUsersWithPermission((string) $args['permission']);

        $data = array();

        foreach($users as $user)
        {
            $roles = PermissionOperator::getRolesByUserAndPermission($user, $args['permission']);

            $roleList = array();

            foreach($roles as $role)
            {
                $roleList[] = array(
                    'id' => $role->getId(),
                    'name' => $role->getName()
                );
            }

            $data[] = array(
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                'roles' => $roleList
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }
}