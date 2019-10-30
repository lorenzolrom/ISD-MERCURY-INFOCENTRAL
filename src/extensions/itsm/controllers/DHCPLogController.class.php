<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 9/19/2019
 * Time: 1:00 PM
 */


namespace extensions\itsm\controllers;


use extensions\itsm\business\DHCPLogOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use models\HTTPRequest;
use models\HTTPResponse;

class DHCPLogController extends Controller
{
    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_dhcplogs-r');

        if($this->request->method() === HTTPRequest::POST)
        {
            return $this->getDHCPLog();
        }

        return NULL;
    }

    private function getDHCPLog(): HTTPResponse
    {
        $args = self::getFormattedBody(array('query', 'lines'), TRUE);
        $log = DHCPLogOperator::getDHCPLog((string)$args['query'], $args['lines']);

        return new HTTPResponse(HTTPResponse::OK, $log);
    }
}