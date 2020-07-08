<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 5/14/2019
 * Time: 9:34 AM
 */


namespace extensions\tickets\controllers;


use extensions\tickets\business\AttributeOperator;
use extensions\tickets\business\WorkspaceOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;
use extensions\tickets\models\Attribute;
use extensions\tickets\models\Ticket;

class AttributeController extends Controller
{
    private const FIELDS = array('type', 'code', 'name');

    private $workspace;

    /**
     * AttributeController constructor.
     * @param string|null $workspace
     * @param HTTPRequest $request
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public function __construct(?string $workspace, HTTPRequest $request)
    {
        $this->workspace = WorkspaceOperator::getWorkspace((int)$workspace);

        parent::__construct($request);
    }

    /**
     * @return HTTPResponse|null
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryInUseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            if($param === NULL)
                return $this->getAll();

            if(in_array($param, Attribute::TYPES))
                return $this->getAllOfType($param);

            return $this->get($param);
        }
        else if($this->request->method() === HTTPRequest::POST)
            return $this->create();
        else if($this->request->method() === HTTPRequest::PUT)
            return $this->update($param);
        else if($this->request->method() === HTTPRequest::DELETE)
            return $this->delete($param);

        return NULL;
    }

    /**
     * @param string $type
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getAllOfType(string $type): HTTPResponse
    {
        $data = array();

        // Display static statuses
        if($type == 'status')
        {
            foreach(array_keys(Ticket::STATIC_STATUSES) as $status)
            {
                $data[] = array(
                    'code' => $status,
                    'name' => Ticket::STATIC_STATUSES[$status]
                );
            }
        }

        foreach (AttributeOperator::getAllOfType($this->workspace, $type) as $attr)
        {
            $data[] = array(
                'id' => $attr->getId(),
                'type' => $attr->getType(),
                'code' => $attr->getCode(),
                'name' => $attr->getName()
                );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getAll(): HTTPResponse
    {
        $data = array();

        foreach(Attribute::TYPES as $type)
        {
            foreach(AttributeOperator::getAllOfType($this->workspace, $type) as $attr)
            {
                $data[] = array(
                    'id' => $attr->getId(),
                    'type' => $attr->getType(),
                    'code' => $attr->getCode(),
                    'name' => $attr->getName()
                );
            }
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function get(?string $param): HTTPResponse
    {
        $attr = AttributeOperator::getById((int)$param);

        return new HTTPResponse(HTTPResponse::OK, array(
            'id' => $attr->getId(),
            'type' => $attr->getType(),
            'code' => $attr->getCode(),
            'name' => $attr->getName()
        ));
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function create(): HTTPResponse
    {
        CurrentUserController::validatePermission('tickets-admin');

        $vals = self::getFormattedBody(self::FIELDS);

        return new HTTPResponse(HTTPResponse::CREATED, array('id' => AttributeOperator::create($this->workspace, $vals)));
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function update(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission('tickets-admin');

        $attr = AttributeOperator::getById((int)$param);
        $vals = self::getFormattedBody(self::FIELDS);

        AttributeOperator::update($attr, $vals);

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
    private function delete(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission('tickets-admin');

        $attr = AttributeOperator::getById((int) $param);
        AttributeOperator::delete($attr);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}
