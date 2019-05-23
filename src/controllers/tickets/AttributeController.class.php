<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/14/2019
 * Time: 9:34 AM
 */


namespace controllers\tickets;


use business\tickets\WorkspaceOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

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
     * @throws \exceptions\SecurityException
     */
    public function __construct(?string $workspace, HTTPRequest $request)
    {
        CurrentUserController::validatePermission('tickets');
        $this->workspace = WorkspaceOperator::getWorkspace((string)$workspace);
        parent::__construct($request);
    }

    /**
     * @return HTTPResponse|null
     */
    public function getResponse(): ?HTTPResponse
    {
        return NULL;
    }

    private function getAllOfType(string $type): HTTPResponse
    {
        $data = array();



        return new HTTPResponse(HTTPResponse::OK, $data);
    }
}