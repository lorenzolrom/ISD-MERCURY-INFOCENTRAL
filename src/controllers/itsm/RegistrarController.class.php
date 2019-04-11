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

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
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
            }
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
            'phone' => $registrar->getPhone(),
            'createDate' => $registrar->getCreateDate(),
            'createUser' => $registrar->getCreateUser(),
            'lastModifyDate' => $registrar->getLastModifyDate(),
            'lastModifyUser' => $registrar->getLastModifyUser()
        );

        return new HTTPResponse(HTTPResponse::OK, $data);
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