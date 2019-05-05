<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/05/2019
 * Time: 4:58 PM
 */


namespace controllers;


use business\NotificationOperator;
use models\HTTPRequest;
use models\HTTPResponse;

class NotificationController extends Controller
{
    private const FIELDS = array('title', 'data', 'important', 'email', 'roles');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('settings');

        if($this->request->method() === HTTPRequest::POST)
            return $this->sendToRoles();

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function sendToRoles(): HTTPResponse
    {
        $args = self::getFormattedBody(self::FIELDS);

        $errors = NotificationOperator::bulkSendToRoles($args);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::CREATED, $errors);
    }
}