<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 6/13/2019
 * Time: 11:38 AM
 */


namespace controllers\lockshop;


use business\lockshop\KeyOperator;
use business\lockshop\SystemOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use models\HTTPRequest;
use models\HTTPResponse;

class KeyController extends Controller
{
    private const FIELDS = array('code', 'quantity', 'keyway', 'bitting');

    /**
     * @return HTTPResponse|null
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::POST)
        {
            CurrentUserController::validatePermission('lockshop-w');
            return $this->createKey($param);
        }

        return NULL;
    }

    /**
     * @param string|null $systemId
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function createKey(?string $systemId): HTTPResponse
    {
        $system = SystemOperator::get((int) $systemId);

        $id = KeyOperator::create($system, self::getFormattedBody(self::FIELDS));

        return new HTTPResponse(HTTPResponse::CREATED, array('id' => $id));
    }
}