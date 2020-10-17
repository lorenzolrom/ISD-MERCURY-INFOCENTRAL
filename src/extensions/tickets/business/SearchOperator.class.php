<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 9/12/2019
 * Time: 6:23 PM
 */


namespace extensions\tickets\business;


use business\Operator;
use extensions\tickets\database\SearchDatabaseHandler;
use extensions\tickets\models\Search;
use extensions\tickets\models\Workspace;
use models\User;
use utilities\HistoryRecorder;

class SearchOperator extends Operator
{
    public const FIELDS = array('name', 'number', 'title', 'contact', 'assignees', 'severity', 'type', 'category',
        'status', 'closureCode', 'desiredDateStart', 'desiredDateEnd', 'scheduledDateStart', 'scheduledDateEnd',
        'description');

    public const SPACE_SYMBOL = '_';

    /**
     * @param int $id
     * @return Search
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getSearch(int $id): Search
    {
        return SearchDatabaseHandler::selectById($id);
    }

    /**
     * @param Workspace $workspace
     * @param User $user
     * @param bool $convertSpaces
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function getAllSearchNamesByUserWorkspace(Workspace $workspace, User $user, bool $convertSpaces = FALSE): array
    {
        $names = SearchDatabaseHandler::selectNamesByUserWorkspace($user->getId(), $workspace->getId());

        if(!$convertSpaces)
            return $names;

        $newNames = array();

        foreach($names as $name)
        {
            $newNames[] = str_replace(' ', self::SPACE_SYMBOL, $name);
        }

        return $newNames;
    }

    /**
     * @param Workspace $workspace
     * @param User $user
     * @param string $name
     * @return Search
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getSearchByUserWorkspaceName(Workspace $workspace, User $user, string $name): Search
    {
        return SearchDatabaseHandler::selectByUserWorkspaceName($user->getId(), $workspace->getId(), $name);
    }

    /**
     * @param Workspace $workspace
     * @param User $user
     * @param array $vals
     * @return Search
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function create(Workspace $workspace, User $user, array $vals): Search
    {
        self::validate('extensions\tickets\models\Search', $vals);

        $search = SearchDatabaseHandler::insert($workspace->getId(), $user->getId(), $vals['name'], $vals['number'], $vals['title'], $vals['contact'], $vals['assignees'],
            $vals['severity'], $vals['type'], $vals['category'], $vals['status'], $vals['closureCode'], $vals['desiredDateStart'], $vals['desiredDateEnd'], $vals['scheduledDateStart'],
            $vals['scheduledDateEnd'], $vals['description']);

        HistoryRecorder::writeHistory('Tickets_Search', HistoryRecorder::CREATE, $search->getId(), $search);

        return $search;
    }

    /**
     * @param Search $search
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function delete(Search $search): bool
    {
        HistoryRecorder::writeHistory('Tickets_Search', HistoryRecorder::DELETE, $search->getId(), $search);

        return SearchDatabaseHandler::delete($search->getId());
    }

    /**
     * @param Search $search
     * @param array $vals
     * @return Search
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function update(Search $search, array $vals): Search
    {
        self::validate('extensions\tickets\models\Search', $vals);

        HistoryRecorder::writeHistory('Tickets_Search', HistoryRecorder::MODIFY, $search->getId(), $search, $vals);

        return SearchDatabaseHandler::update($search->getId(), $vals['number'], $vals['title'], $vals['contact'], $vals['assignees'],
            $vals['severity'], $vals['type'], $vals['category'], $vals['status'], $vals['closureCode'], $vals['desiredDateStart'], $vals['desiredDateEnd'], $vals['scheduledDateStart'],
            $vals['scheduledDateEnd'], $vals['description']);
    }
}
