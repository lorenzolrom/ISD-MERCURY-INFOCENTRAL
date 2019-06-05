<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 6/05/2019
 * Time: 9:17 AM
 */


namespace controllers\tickets;


use business\tickets\WorkspaceOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use exceptions\SecurityException;
use models\HTTPRequest;
use models\HTTPResponse;

/**
 * Class TicketController
 *
 * Controller for accessing tickets
 *
 * @package controllers\tickets
 */
class TicketController extends Controller
{
    private $workspace;

    /**
     * TicketController constructor.
     * @param string $workspace
     * @param HTTPRequest $request
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function __construct(string $workspace, HTTPRequest $request)
    {
        $this->workspace = WorkspaceOperator::getWorkspace((int)$workspace);

        // User must be in a team assigned to workspace
        if(!WorkspaceOperator::currentUserInWorkspace($this->workspace))
            throw new SecurityException('You are not a member of this workspace', SecurityException::USER_NO_PERMISSION);

        parent::__construct($request);
    }

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('tickets-agent');

        return NULL;
    }
}