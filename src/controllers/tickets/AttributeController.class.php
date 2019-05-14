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
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class AttributeController extends Controller
{
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
}