<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 9/15/2019
 * Time: 8:49 PM
 */


namespace extensions\tickets\business;


use business\Operator;
use controllers\CurrentUserController;
use extensions\tickets\database\WidgetDatabaseHandler;
use extensions\tickets\models\Search;
use extensions\tickets\models\Widget;
use extensions\tickets\models\Workspace;

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
