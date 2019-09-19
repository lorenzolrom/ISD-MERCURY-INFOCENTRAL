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


namespace controllers\itsm;


use business\itsm\DHCPLogOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryInUseException;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
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

        if($this->request->method() === HTTPRequest::GET)
        {
            return $this->getDHCPLog();
        }

        return NULL;
    }

    private function getDHCPLog(): HTTPResponse
    {
        $log = DHCPLogOperator::getDHCPLog();

        return new HTTPResponse(HTTPResponse::OK, array($log));
    }
}