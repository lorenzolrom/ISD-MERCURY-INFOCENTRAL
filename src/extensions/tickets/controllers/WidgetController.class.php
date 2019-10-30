<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 9/15/2019
 * Time: 8:52 PM
 */


namespace extensions\tickets\controllers;


use extensions\tickets\business\SearchOperator;
use extensions\tickets\business\WidgetOperator;
use extensions\tickets\business\WorkspaceOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use models\HTTPRequest;
use models\HTTPResponse;
use utilities\HistoryRecorder;

class WidgetController extends Controller
{
    private $workspace;

    /**
     * WidgetController constructor.
     * @param string|null $param
     * @param HTTPRequest $request
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function __construct(?string $param, HTTPRequest $request)
    {
        CurrentUserController::validatePermission('tickets-agent');

        $this->workspace = WorkspaceOperator::getWorkspace((int)$param);
        parent::__construct($request);
    }

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws ValidationError
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            return $this->getWidgets();
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            return $this->deleteWidget((int)$param);
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            return $this->createWidget();
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function getWidgets(): HTTPResponse
    {
        $data = array();

        foreach(WidgetOperator::getWidgets($this->workspace) as $widget)
        {
            $widgetInfo = array();
            $search = SearchOperator::getSearch($widget->getSearch());

            $widgetInfo['id'] = $widget->getId();
            $widgetInfo['search'] = $search->getName();

            $data[] = $widgetInfo;
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function deleteWidget(int $id): HTTPResponse
    {
        $widget = WidgetOperator::getWidget($id);

        if($widget->getWorkspace() !== $this->workspace->getId() OR $widget->getUser() !== CurrentUserController::currentUser()->getId())
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        HistoryRecorder::writeHistory('Tickets_Widget', HistoryRecorder::DELETE, $widget->getId(), $widget);
        WidgetOperator::delete($widget);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function createWidget(): HTTPResponse
    {
        $search = SearchOperator::getSearchByUserWorkspaceName($this->workspace, CurrentUserController::currentUser(), self::getFormattedBody(array('search'), TRUE)['search']);

        if($search->getUser() !== CurrentUserController::currentUser()->getId() OR $search->getWorkspace() !== $this->workspace->getId())
            throw new ValidationError(array('Search not found'));

        $widget = WidgetOperator::create($this->workspace, $search);
        HistoryRecorder::writeHistory('Tickets_Widget', HistoryRecorder::CREATE, $widget->getId(), $widget);

        return new HTTPResponse(HTTPResponse::CREATED, array('id' => $widget->getId()));
    }
}