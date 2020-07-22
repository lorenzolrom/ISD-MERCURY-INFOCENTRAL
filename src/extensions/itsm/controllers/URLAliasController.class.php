<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/02/2019
 * Time: 9:16 PM
 */


namespace extensions\itsm\controllers;


use extensions\itsm\business\URLAliasOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class URLAliasController extends Controller
{
    private const FIELDS = array('alias', 'destination', 'disabled');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_web-aliases-rw');

        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            switch($param)
            {
                case null:
                    return $this->search();
                default:
                    return $this->getURLAlias($param);
            }
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            switch($param)
            {
                case 'search':
                    return $this->search(TRUE);
                default:
                    return $this->createURLAlias();
            }
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            return $this->updateURLAlias($param);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            return $this->deleteURLAlias($param);
        }

        return NULL;
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getURLAlias(?string $param): HTTPResponse
    {
        $alias = URLAliasOperator::getURLAlias((int) $param);

        return new HTTPResponse(HTTPResponse::OK, array(
            'id' => $alias->getId(),
            'alias' => $alias->getAlias(),
            'destination' => $alias->getDestination(),
            'disabled' => $alias->getDisabled()
        ));
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function createURLAlias(): HTTPResponse
    {
        $args = $this->getFormattedBody(self::FIELDS);

        $errors = URLAliasOperator::createURLAlias($args['alias'], $args['destination'], $args['disabled']);

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
    private function updateURLAlias(?string $param): HTTPResponse
    {
        $urlAlias = URLAliasOperator::getURLAlias((int)$param);

        $args = $this->getFormattedBody(self::FIELDS);
        $errors = URLAliasOperator::updateURLAlias($urlAlias, $args['alias'], $args['destination'], $args['disabled']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function deleteURLAlias(?string $param): HTTPResponse
    {
        $urlAlias = URLAliasOperator::getURLAlias((int)$param);

        URLAliasOperator::deleteURLAlias($urlAlias);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param bool $search
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function search(bool $search = FALSE): HTTPResponse
    {
        if($search)
        {
            $args = $this->getFormattedBody(self::FIELDS, FALSE);
            $aliases = URLAliasOperator::search($args['alias'], $args['destination'], $args['disabled']);
        }
        else
        {
            $aliases = URLAliasOperator::search();
        }

        $data = array();

        foreach($aliases as $alias)
        {
            $data[] = array(
                'id' => $alias->getId(),
                'alias' => $alias->getAlias(),
                'destination' => $alias->getDestination(),
                'disabled' => $alias->getDisabled()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }
}
