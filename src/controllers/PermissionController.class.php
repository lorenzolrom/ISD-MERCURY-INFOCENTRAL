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
}