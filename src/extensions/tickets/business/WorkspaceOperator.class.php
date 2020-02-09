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


namespace extensions\tickets\business;


use business\Operator;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use extensions\tickets\database\WorkspaceDatabaseHandler;
use exceptions\EntryInUseException;
use extensions\tickets\models\Workspace;
use models\Secret;
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
        self::validate('extensions\tickets\models\Workspace', $vals);

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
            self::validate('extensions\tickets\models\Workspace', $vals);
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

    /**
     * @return Workspace
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getRequestPortal(): Workspace
    {
        return WorkspaceDatabaseHandler::selectRequestPortal();
    }

    /**
     * @param Workspace $workspace
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function currentUserInWorkspace(Workspace $workspace): bool
    {
        $user = CurrentUserController::currentUser();

        foreach($workspace->getTeams() as $team)
        {
            foreach($team->getUsers() as $member)
            {
                if($user->getId() === $member->getId())
                    return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * @param Workspace $workspace
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function getAssigneeList(Workspace $workspace): array
    {
        $assignees = array();

        foreach($workspace->getTeams() as $team)
        {
            $teamArray = array();

            $teamArray['id'] = $team->getId();
            $teamArray['name'] = $team->getName();
            $teamArray['users'] = array();

            foreach($team->getUsers() as $user)
            {
                $userArray = array();

                $userArray['id'] = $user->getId();
                $userArray['username'] = $user->getUsername();
                $userArray['name'] = $user->getFirstName() . " " . $user->getLastName();

                $teamArray['users'][] = $userArray;
            }

            $assignees[] = $teamArray;
        }

        return $assignees;
    }

    /**
     * @param Workspace $workspace
     * @param Secret $secret
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function isSecretAllowed(Workspace $workspace, Secret $secret): bool
    {
        return WorkspaceDatabaseHandler::isSecretAllowed($workspace->getId(), $secret->getId());
    }

    /**
     * @param Workspace $workspace
     * @param Secret $secret
     * @return bool
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function addSecret(Workspace $workspace, Secret $secret): bool
    {
        $h = HistoryRecorder::writeHistory('Tickets_Workspace', HistoryRecorder::MODIFY, $workspace->getId(), $workspace);
        HistoryRecorder::writeAssocHistory($h, array('systemEntry' => array('Add Secret: ' . $secret->getName())));

        return WorkspaceDatabaseHandler::addSecret($workspace->getId(), $secret->getId());
    }

    /**
     * @param Workspace $workspace
     * @param Secret $secret
     * @return bool
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function delSecret(Workspace $workspace, Secret $secret): bool
    {
        $h = HistoryRecorder::writeHistory('Tickets_Workspace', HistoryRecorder::MODIFY, $workspace->getId(), $workspace);
        HistoryRecorder::writeAssocHistory($h, array('systemEntry' => array('Remove Secret: ' . $secret->getName())));

        return WorkspaceDatabaseHandler::delSecret($workspace->getId(), $secret->getId());
    }
}