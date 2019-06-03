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
use exceptions\EntryInUseException;
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
     * @throws EntryInUseException
     */
    public static function delete(Workspace $workspace): array
    {
        if(WorkspaceDatabaseHandler::hasTickets($workspace->getId()))
            throw new EntryInUseException(EntryInUseException::MESSAGES[EntryInUseException::ENTRY_IN_USE], EntryInUseException::ENTRY_IN_USE);

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
        self::validate('models\tickets\Workspace', $vals);

        $workspace = WorkspaceDatabaseHandler::insert($vals['name']);

        if($vals['teams'] === NULL)
            $vals['teams'] = array();

        $history = HistoryRecorder::writeHistory('Tickets_Workspace', HistoryRecorder::CREATE, $workspace->getId(), $workspace);

        HistoryRecorder::writeAssocHistory($history, array('team' => $vals['teams']));
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
        if($workspace->getName() != (string)$vals['name']) // Only check name if it has been changed
        {
            self::validate('models\tickets\Workspace', $vals);
        }

        if($vals['teams'] === NULL)
            $vals['teams'] = array();

        $history = HistoryRecorder::writeHistory('Tickets_Workspace', HistoryRecorder::MODIFY, $workspace->getId(), $workspace, $vals);
        WorkspaceDatabaseHandler::update($workspace->getId(), $vals['name']);

        HistoryRecorder::writeAssocHistory($history, array('team' => $vals['teams']));
        WorkspaceDatabaseHandler::setTeams($workspace->getId(), $vals['teams']);

        return array('id' => $workspace->getId());
    }

    /**
     * @param Workspace $workspace
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function setRequestPortal(Workspace $workspace): bool
    {
        HistoryRecorder::writeHistory('Tickets_Workspace', HistoryRecorder::MODIFY, $workspace->getId(), $workspace, array('requestPortal' => 1));
        WorkspaceDatabaseHandler::setRequestPortal($workspace->getId());

        return TRUE;
    }
}