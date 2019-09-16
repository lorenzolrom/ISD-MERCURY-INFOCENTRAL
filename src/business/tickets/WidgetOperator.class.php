<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 9/15/2019
 * Time: 8:49 PM
 */


namespace business\tickets;


use business\Operator;
use controllers\CurrentUserController;
use database\tickets\WidgetDatabaseHandler;
use models\tickets\Search;
use models\tickets\Widget;
use models\tickets\Workspace;

class WidgetOperator extends Operator
{
    /**
     * @param Workspace $workspace
     * @return Widget[]
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function getWidgets(Workspace $workspace): array
    {
        return WidgetDatabaseHandler::selectByUserWorkspace(CurrentUserController::currentUser()->getId(), $workspace->getId());
    }

    /**
     * @param int $id
     * @return Widget
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getWidget(int $id): Widget
    {
        return WidgetDatabaseHandler::selectById($id);
    }

    /**
     * @param Workspace $workspace
     * @param Search $search
     * @return Widget
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function create(Workspace $workspace, Search $search): Widget
    {
        return WidgetDatabaseHandler::insert(CurrentUserController::currentUser()->getId(), $workspace->getId(), $search->getId());
    }

    /**
     * @param Widget $widget
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(Widget $widget): bool
    {
        return WidgetDatabaseHandler::delete($widget->getId());
    }
}