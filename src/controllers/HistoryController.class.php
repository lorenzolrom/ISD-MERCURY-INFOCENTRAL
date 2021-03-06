<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/27/2019
 * Time: 10:38 PM
 */


namespace controllers;


use business\HistoryOperator;
use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use exceptions\RouteException;
use exceptions\SecurityException;
use models\HTTPRequest;
use models\HTTPResponse;
use utilities\HistoryRecorder;

class HistoryController extends Controller
{
    private const FIELDS = array('object', 'index', 'action', 'username');

    /**
     * @return HTTPResponse|null
     * @throws DatabaseException
     * @throws SecurityException
     * @throws RouteException
     * @throws EntryNotFoundException
     */
    public function getResponse(): ?HTTPResponse
    {
        if($this->request->method() === HTTPRequest::GET)
        {
            if($this->request->next() === 'objects')
                return $this->getHistoryObjects();

            return $this->getHistory();
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws DatabaseException
     * @throws SecurityException
     * @throws RouteException
     * @throws EntryNotFoundException
     */
    private function getHistory(): HTTPResponse
    {
        $args = $this->getFormattedBody(self::FIELDS, TRUE);

        if($args['object'] === NULL OR $args['index'] === NULL)
            throw new RouteException(RouteException::MESSAGES[RouteException::REQUIRED_PARAMETER_IS_INVALID], RouteException::REQUIRED_PARAMETER_IS_INVALID);

        if($args['action'] === NULL OR $args['action'] === "")
            $args['action'] = '%';
        if($args['username'] === NULL OR $args['username'] === "")
            $args['username'] = '%';
        if($args['index'] === NULL OR $args['index'] === "")
            $args['index'] = '%';

        $data = array();

        foreach(HistoryOperator::getHistory($args['object'], $args['index'], $args['action'], $args['username']) as $history)
        {
            // Skip returning this record if it does not contain any changes, and is not a delete record
            if(empty($history->getItems()) AND ($history->getAction() !== HistoryRecorder::DELETE))
                continue;

            $data[] = array(
                'id' => $history->getId(),
                'index' => $history->getIndex(),
                'username' => $history->getUsername(),
                'time' => $history->getTime(),
                'action' => $history->getAction(),
                'changes' => $history->getItems()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * Return a list of history object types that the current user has access to search
     * @return HTTPResponse
     * @throws DatabaseException
     * @throws SecurityException
     */
    private function getHistoryObjects(): HTTPResponse
    {
        return new HTTPResponse(HTTPResponse::OK, HistoryOperator::getHistoryObjects());
    }
}
