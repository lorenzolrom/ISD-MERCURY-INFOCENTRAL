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
use database\tickets\TeamDatabaseHandler;
use models\tickets\Team;
use utilities\HistoryRecorder;

class TeamOperator extends Operator
{
    private const FIELDS = array('name', 'users');

    /**
     * @param int $id
     * @return Team
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getWorkspace(int $id): Team
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
        $errors = self::validate('models\tickets\Team', $vals);

        if(!empty($errors))
            return array('errors' => $errors);

        $team = TeamDatabaseHandler::insert($vals['name']);
        $history = HistoryRecorder::writeHistory('Tickets_Team', HistoryRecorder::CREATE, $team->getId(), $team);

        HistoryRecorder::writeAssocHistory($history, $vals['users']);
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
            $errors = self::validate('models\tickets\Team', $vals);

            if(!empty($errors))
                return array('errors' => $errors);
        }

        $history = HistoryRecorder::writeHistory('Tickets_Team', HistoryRecorder::MODIFY, $team->getId(), $team, $vals);
        HistoryRecorder::writeAssocHistory($history, $vals['users']);

        TeamDatabaseHandler::update($team->getId(), $vals['name']);
        TeamDatabaseHandler::setUsers($team->getId(), $vals['users']);

        return array('id' => $team->getId());
    }
}