<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/14/2019
 * Time: 9:23 AM
 */


namespace controllers\tickets;


use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryInUseException;
use exceptions\EntryNotFoundException;
use models\HTTPResponse;

/**
 * Class TicketsController
 *
 * Controller for parent /tickets route
 *
 * @package controllers\tickets
 */
class TicketsController extends Controller
{

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws EntryInUseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('tickets');

        $param = $this->request->next();

        switch($param)
        {
            case 'requests':
                $r = new RequestController($this->request);
                return $r->getResponse();
            case 'workspaces':
                $w = new WorkspaceController($this->request);
                return $w->getResponse();
            case 'teams':
                $t = new TeamController($this->request);
                return $t->getResponse();
        }

        return NULL;
    }

    public static function validateWorkspaceMembership(): bool
    {
        return TRUE;
    }
}