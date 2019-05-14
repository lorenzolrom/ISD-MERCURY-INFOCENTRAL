<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/13/2019
 * Time: 6:49 PM
 */


namespace business\tickets;


use business\Operator;
use database\tickets\WorkspaceDatabaseHandler;
use models\tickets\Workspace;
use utilities\HistoryRecorder;

class WorkspaceOperator extends Operator
{
    /**
     * @param int $id
     * @return Workspace
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getWorkspace(int $id): Workspace
    {
        return WorkspaceDatabaseHandler::selectById($id);
    }

    /**
     * @return Workspace[]
     * @throws \exceptions\DatabaseException
     */
    public static function getAll(): array
    {
        return WorkspaceDatabaseHandler::select();
    }

    /**
     * @param Workspace $workspace
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function delete(Workspace $workspace): array
    {
        if(WorkspaceDatabaseHandler::hasTickets($workspace->getId()))
            return array('errors' => array('Workspace with tickets cannot be deleted'));

        HistoryRecorder::writeHistory('Tickets_Workspace', HistoryRecorder::DELETE, $workspace->getId(), $workspace);

        WorkspaceDatabaseHandler::delete($workspace->getId());

        return array();
    }

    /**
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function create(array $vals): array
    {
        $errors = self::validate('models\tickets\Workspace', $vals);

        if(!empty($errors))
            return array('errors' => $errors);

        $workspace = WorkspaceDatabaseHandler::insert($vals['name']);

        $history = HistoryRecorder::writeHistory('Tickets_Workspace', HistoryRecorder::CREATE, $workspace->getId(), $workspace);

        HistoryRecorder::writeAssocHistory($history, $vals['teams']);
        WorkspaceDatabaseHandler::setTeams($workspace->getId(), $vals['teams']);

        return array('id' => $workspace->getId());
    }

    /**
     * @param Workspace $workspace
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function update(Workspace $workspace, array $vals): array
    {
        if(isset($vals['name']) AND $workspace->getName() != (string)$vals['name']) // Only check name if it has been changed
        {
            $errors = self::validate('models\tickets\Workspace', $vals);

            if(!empty($errors))
                return array('errors' => $errors);
        }

        $history = HistoryRecorder::writeHistory('Tickets_Workspace', HistoryRecorder::MODIFY, $workspace->getId(), $workspace, $vals);
        WorkspaceDatabaseHandler::update($workspace->getId(), $vals['name']);

        HistoryRecorder::writeAssocHistory($history, $vals['teams']);
        WorkspaceDatabaseHandler::setTeams($workspace->getId(), $vals['teams']);

        return array('id' => $workspace->getId());
    }
}