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


use business\lockshop\SystemOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use models\HTTPRequest;
use models\HTTPResponse;

class SystemController extends Controller
{
    private const FIELDS = array('name', 'code');

    /**
     * @return HTTPResponse|null
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('lockshop-r');

        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            if($param === NULL) // Search
                return $this->search();

            $param2 = $this->request->next();

            if($param2 === NULL)
                return $this->getSystem($param);
            else if($param2 == 'cores')
                return $this->getSystemCores($param);
            else if($param2 == 'keys')
                return $this->getSystemKeys($param);
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            if($param == 'search')
                return $this->search(TRUE);
            else if($param === NULL)
            {
                CurrentUserController::validatePermission('lockshop-w');
                return $this->create();
            }
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            CurrentUserController::validatePermission('lockshop-w');
            return $this->update($param);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            CurrentUserController::validatePermission('lockshop-w');
            return $this->delete($param);
        }

        return NULL;
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getSystem(?string $param): HTTPResponse {
        $system = SystemOperator::get((int)$param);

        return new HTTPResponse(HTTPResponse::OK, array(
            'id' => $system->getId(),
            'name' => $system->getName(),
            'code' => $system->getCode()
        ));
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getSystemCores(?string $param): HTTPResponse {
        $system = SystemOperator::get((int)$param);

        $data = array();

        foreach($system->getCores() as $core)
        {
            $data[] = array(
                'id' => $core->getId(),
                'code' => $core->getCode(),
                'quantity' => $core->getQuantity()
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
    private function getSystemKeys(?string $param): HTTPResponse {
        $system = SystemOperator::get((int)$param);

        $data = array();

        foreach($system->getKeys() as $key)
        {
            $data[] = array(
                'id' => $key->getId(),
                'code' => $key->getCode(),
                'quantity' => $key->getQuantity()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param bool $search
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function search(bool $search = FALSE): HTTPResponse
    {
        if($search)
            $systems = SystemOperator::search(self::getFormattedBody(self::FIELDS, FALSE));
        else
            $systems = SystemOperator::search();

        $data = array();

        foreach($systems as $system)
        {
            $data[] = array(
                'id' => $system->getId(),
                'code' => $system->getCode(),
                'name' => $system->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function create(): HTTPResponse {
        $system = SystemOperator::create(self::getFormattedBody(self::FIELDS));

        return new HTTPResponse(HTTPResponse::CREATED, array('id' => $system));
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function update(?string $param): HTTPResponse {

        $system = SystemOperator::get((int)$param);

        SystemOperator::update($system, self::getFormattedBody(self::FIELDS));

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function delete(?string $param): HTTPResponse {
        $system = SystemOperator::get((int)$param);

        SystemOperator::delete($system);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}