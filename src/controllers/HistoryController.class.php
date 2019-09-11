<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/27/2019
 * Time: 10:38 PM
 */


namespace controllers;


use business\HistoryOperator;
use exceptions\RouteException;
use models\HTTPRequest;
use models\HTTPResponse;

class HistoryController extends Controller
{
    private const FIELDS = array('object', 'index', 'action', 'username');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws RouteException
     * @throws \exceptions\EntryNotFoundException
     */
    public function getResponse(): ?HTTPResponse
    {
        if($this->request->method() === HTTPRequest::GET)
            return $this->getHistory();
        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws RouteException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getHistory(): HTTPResponse
    {
        $args = $this->getFormattedBody(self::FIELDS, TRUE);

        if($args['object'] === NULL OR $args['index'] === NULL)
            throw new RouteException(RouteException::MESSAGES[RouteException::REQUIRED_PARAMETER_IS_INVALID], RouteException::REQUIRED_PARAMETER_IS_INVALID);

        if($args['action'] === NULL)
            $args['action'] = '%';
        if($args['username'] === NULL)
            $args['username'] = '%';

        $data = array();

        foreach(HistoryOperator::getHistory($args['object'], $args['index'], $args['action'], $args['username']) as $history)
        {
            $data[] = array(
                'id' => $history->getId(),
                'username' => $history->getUsername(),
                'time' => $history->getTime(),
                'action' => $history->getAction(),
                'changes' => $history->getItems()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }
}