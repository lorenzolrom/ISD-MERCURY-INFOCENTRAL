<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/21/2019
 * Time: 10:41 AM
 */


namespace controllers;


use business\BadLoginOperator;
use models\HTTPRequest;
use models\HTTPResponse;

class BadLoginController extends Controller
{
    private const FIELDS = array('username', 'ipAddress', 'timeStart', 'timeEnd');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('settings');

        if($this->request->method() === HTTPRequest::POST)
        {
            if($this->request->next() === 'search')
                return $this->search();
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function search(): HTTPResponse
    {
        $args = self::getFormattedBody(self::FIELDS);

        $logins = BadLoginOperator::search("%{$args['username']}%", "%{$args['ipAddress']}%", $args['timeStart'], $args['timeEnd']);

        return new HTTPResponse(HTTPResponse::OK, $logins);
    }
}