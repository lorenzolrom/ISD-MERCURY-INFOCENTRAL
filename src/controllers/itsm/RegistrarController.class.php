<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/06/2019
 * Time: 10:44 AM
 */


namespace controllers\itsm;


use business\itsm\RegistrarOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class RegistrarController extends Controller
{
    const SEARCH_FIELDS = array('code', 'name');
    const FIELDS = array('code', 'name', 'url', 'phone');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\EntryInUseException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_web-registrars-r');

        $param = $this->request->next();

        if($this->request->method() == HTTPRequest::GET)
        {
            switch($param)
            {
                case null:
                    return $this->getSearchResult();
                default:
                    return $this->getById($param);

            }
        }
        else if($this->request->method() == HTTPRequest::POST)
        {
            switch($param)
            {
                case "search":
                    return $this->getSearchResult(TRUE);
                default:
                    return $this->createRegistrar();
            }
        }
        else if($this->request->method() == HTTPRequest::DELETE)
        {
            return $this->deleteRegistrar($param);
        }
        else if($this->request->method() == HTTPRequest::PUT)
        {
            return $this->updateRegistrar($param);
        }

        return NULL;
    }

    /**
     * @param string $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getById(string $param): HTTPResponse
    {
        $registrar = RegistrarOperator::getRegistrar((int)$param);

        $data = array(
            'id' => $registrar->getId(),
            'code' => $registrar->getCode(),
            'name' => $registrar->getName(),
            'url' => $registrar->getUrl(),
            'phone' => $registrar->getPhone()
        );

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function createRegistrar(): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_web-registrars-w'));

        $args = $this->getFormattedBody(self::FIELDS, TRUE);

        $errors = RegistrarOperator::createRegistrar($args['code'], $args['name'], $args['url'], $args['phone']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::CREATED, $errors);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function updateRegistrar(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_web-registrars-w'));

        $registrar = RegistrarOperator::getRegistrar((int) $param);

        $args = $this->getFormattedBody(self::FIELDS, TRUE);

        $errors = RegistrarOperator::updateRegistrar($registrar, $args['code'], $args['name'], $args['url'], $args['phone']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryInUseException
     * @throws \exceptions\SecurityException
     */
    private function deleteRegistrar(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_web-registrars-w'));

        $registrar = RegistrarOperator::getRegistrar((int) $param);
        RegistrarOperator::deleteRegistrar($registrar);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param bool $search
     * @param bool $strict
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    public function getSearchResult(bool $search = FALSE, bool $strict = FALSE): HTTPResponse
    {
        if($search)
        {
            $args = $this->getFormattedBody(self::SEARCH_FIELDS, $strict);

            $registrars = RegistrarOperator::search($args['code'], $args['name']);
        }
        else
            $registrars = RegistrarOperator::search();

        $results = array();

        foreach($registrars as $registrar)
        {
            $results[] = array(
                'id' => $registrar->getId(),
                'code' => $registrar->getCode(),
                'name' => $registrar->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $results);
    }
}