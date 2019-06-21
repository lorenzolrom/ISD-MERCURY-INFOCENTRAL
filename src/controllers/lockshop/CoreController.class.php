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


use business\lockshop\CoreOperator;
use business\lockshop\SystemOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use models\HTTPRequest;
use models\HTTPResponse;

class CoreController extends Controller
{
    private const FIELDS = array('code', 'quantity');

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
            return $this->createCore($param);
        }
        else if($this->request->method() === HTTPRequest::GET)
        {
            if($param !== NULL)
                return $this->getCore($param);
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            CurrentUserController::validatePermission('lockshop-w');
            if($param !== NULL)
                return $this->editCore($param);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            CurrentUserController::validatePermission('lockshop-w');
            if($param !== NULL)
                return $this->deleteCore($param);
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
    private function createCore(?string $systemId): HTTPResponse
    {
        $system = SystemOperator::get((int) $systemId);

        $id = CoreOperator::create($system, self::getFormattedBody(self::FIELDS));

        return new HTTPResponse(HTTPResponse::CREATED, array('id' => $id));
    }

    /**
     * @param string|null $id
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getCore(?string $id): HTTPResponse
    {
        $core = CoreOperator::get((int)$id);

        return new HTTPResponse(HTTPResponse::OK, array(
            'id' => $core->getId(),
            'system' => $core->getSystem(),
            'quantity' => $core->getQuantity(),
            'code' => $core->getCode()
        ));
    }

    /**
     * @param string|null $id
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function editCore(?string $id): HTTPResponse {
        $core = CoreOperator::get((int)$id);

        CoreOperator::update($core, self::getFormattedBody(self::FIELDS));

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $id
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function deleteCore(?string $id): HTTPResponse {
        $core = CoreOperator::get((int)$id);
        CoreOperator::delete($core);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}