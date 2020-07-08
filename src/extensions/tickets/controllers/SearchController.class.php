<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 9/13/2019
 * Time: 7:19 AM
 */


namespace extensions\tickets\controllers;


use extensions\tickets\business\SearchOperator;
use extensions\tickets\business\WorkspaceOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use exceptions\SecurityException;
use models\HTTPRequest;
use models\HTTPResponse;
use extensions\tickets\models\Search;

class SearchController extends Controller
{
    private $workspace;
    private $user;

    /**
     * SearchController constructor.
     * @param string $workspace
     * @param HTTPRequest $request
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     */
    public function __construct(string $workspace, HTTPRequest $request)
    {
        CurrentUserController::validatePermission('tickets-agent'); // ALL AGENTS

        $this->workspace = WorkspaceOperator::getWorkspace((int)$workspace);
        $this->user = CurrentUserController::currentUser();

        // User must be in a team assigned to workspace
        if(!WorkspaceOperator::currentUserInWorkspace($this->workspace))
            throw new SecurityException('You are not a member of this workspace', SecurityException::USER_NO_PERMISSION);

        parent::__construct($request);
    }

    /**
     * @return HTTPResponse|null
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::POST)
        {
            $name = self::getFormattedBody(SearchOperator::FIELDS)['name'];

            try// If name does exist, update existing
            {
                $search = SearchOperator::getSearchByUserWorkspaceName($this->workspace, $this->user, (string)$name);
                return $this->update($search);
            }
            catch(EntryNotFoundException $e)// If name does not exist, create new
            {
                return $this->create();
            }
        }
        else if($this->request->method() === HTTPRequest::GET)
        {
            // List all searches for user
            if($param === NULL OR strlen($param) === 0)
            {
                return $this->getAll();
            }
            else // Get specific search for update
            {
                return $this->get($param);
            }
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            return $this->delete($param);
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationError
     */
    private function create(): HTTPResponse
    {
        return new HTTPResponse(HTTPResponse::CREATED, array('name' => SearchOperator::create($this->workspace, $this->user, self::getFormattedBody(self::getFormattedBody(SearchOperator::FIELDS, TRUE)))));
    }

    /**
     * @param Search $search
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationError
     */
    private function update(Search $search): HTTPResponse
    {
        SearchOperator::update($search, self::getFormattedBody(SearchOperator::FIELDS, TRUE));

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string $name
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     */
    private function delete(string $name): HTTPResponse
    {
        $name = str_replace('_', ' ', $name); // Undo any name encoding from the url
        $search = SearchOperator::getSearchByUserWorkspaceName($this->workspace, $this->user, $name);

        SearchOperator::delete($search);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getAll(): HTTPResponse
    {
        return new HTTPResponse(HTTPResponse::OK, SearchOperator::getAllSearchNamesByUserWorkspace($this->workspace, $this->user, TRUE));
    }

    /**
     * @param string $name
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function get(string $name): HTTPResponse
    {
        $name = str_replace('_', ' ', $name); // Undo any name encoding from the url

        $search = SearchOperator::getSearchByUserWorkspaceName($this->workspace, $this->user, $name);

        return new HTTPResponse(HTTPResponse::OK, array(
            'name' => $search->getName(),
            'number' => $search->getNumber(),
            'title' => $search->getTitle(),
            'contact' => $search->getContact(),
            'assignees' => $search->getAssignees(),
            'severity' => $search->getSeverity(),
            'type' => $search->getType(),
            'category' => $search->getCategory(),
            'status' => $search->getStatus(),
            'closureCode' => $search->getClosureCode(),
            'desiredDateStart' => $search->getDesiredDateStart(),
            'desiredDateEnd' => $search->getDesiredDateEnd(),
            'scheduledDateStart' => $search->getScheduledDateStart(),
            'scheduledDateEnd' => $search->getScheduledDateEnd(),
            'description' => $search->getDescription()
        ));
    }
}
