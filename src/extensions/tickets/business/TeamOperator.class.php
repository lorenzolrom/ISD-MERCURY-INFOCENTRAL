<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 5/13/2019
 * Time: 6:49 PM
 */


namespace extensions\tickets\business;


use business\Operator;
use controllers\CurrentUserController;
use extensions\tickets\database\TeamDatabaseHandler;
use extensions\tickets\models\Team;
use utilities\HistoryRecorder;

class TeamOperator extends Operator
{
    /**
     * @param int $id
     * @return Team
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getTeam(int $id): Team
    {
        return TeamDatabaseHandler::selectById($id);
    }

    /**
     * @return Team[]
     * @throws \exceptions\DatabaseException
     */
    public static function getAll(): array
    {
        return TeamDatabaseHandler::select();
    }

    /**
     * @param Team $team
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function delete(Team $team): array
    {
        CurrentUserController::validatePermission('tickets-admin');
        HistoryRecorder::writeHistory('Tickets_Team', HistoryRecorder::DELETE, $team->getId(), $team);

        TeamDatabaseHandler::delete($team->getId());

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
        self::validate('extensions\tickets\models\Team', $vals);

        $team = TeamDatabaseHandler::insert($vals['name']);
        $history = HistoryRecorder::writeHistory('Tickets_Team', HistoryRecorder::CREATE, $team->getId(), $team);

        if($vals['users'] === NULL)
            $vals['users'] = array();

        HistoryRecorder::writeAssocHistory($history, array('users' => $vals['users']));
        TeamDatabaseHandler::setUsers($team->getId(), $vals['users']);

        return array('id' => $team->getId());
    }

    /**
     * @param Team $team
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function update(Team $team, array $vals): array
    {
        if(isset($vals['name']) AND $team->getName() != (string)$vals['name']) // Only check name if it has been changed
        {
            self::validate('extensions\tickets\models\Team', $vals);
        }

        if($vals['users'] === NULL)
            $vals['users'] = array();

        $history = HistoryRecorder::writeHistory('Tickets_Team', HistoryRecorder::MODIFY, $team->getId(), $team, $vals);
        HistoryRecorder::writeAssocHistory($history, array('user' => $vals['users']));

        TeamDatabaseHandler::update($team->getId(), $vals['name']);
        TeamDatabaseHandler::setUsers($team->getId(), $vals['users']);

        return array('id' => $team->getId());
    }
}
