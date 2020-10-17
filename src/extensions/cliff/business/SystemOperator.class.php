<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/02/2020
 * Time: 7:33 PM
 */


namespace extensions\cliff\business;


use business\Operator;
use exceptions\ValidationError;
use extensions\cliff\database\CoreDatabaseHandler;
use extensions\cliff\database\KeyDatabaseHandler;
use extensions\cliff\database\SystemDatabaseHandler;
use extensions\cliff\models\System;
use utilities\HistoryRecorder;

class SystemOperator extends Operator
{
    /**
     * @param array $vals
     * @return array // An array containing the new System ID
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function create(array $vals): array
    {
        self::validate('extensions\cliff\models\System', $vals); // Check if values are OK

        // Check if code is in use
        if(SystemDatabaseHandler::isCodeInUse($vals['code']))
            throw new ValidationError(array('Code is in use'));

        $system = SystemDatabaseHandler::insert($vals['code'], $vals['name']);

        HistoryRecorder::writeHistory('CLIFF_System', HistoryRecorder::CREATE, $system->getId(), $system);

        return array('id' => $system->getId());
    }

    /**
     * @param System $system
     * @param array $vals
     * @return bool
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function update(System $system, array $vals): bool
    {
        self::validate('extensions\cliff\models\System', $vals); // Check if values are OK

        // Check if code is inuse
        if(($system->getCode() !== $vals['code']) AND SystemDatabaseHandler::isCodeInUse($vals['code']))
            throw new ValidationError(array('Code is in use'));

        HistoryRecorder::writeHistory('CLIFF_System', HistoryRecorder::MODIFY, $system->getId(), $system, $vals);
        return SystemDatabaseHandler::update($system->getId(), $vals['code'], $vals['name']);
    }

    /**
     * @param System $system
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function delete(System $system): bool
    {
        // Delete cores
        foreach(CoreDatabaseHandler::selectBySystem($system->getId()) as $core)
            CoreOperator::delete($core);

        // Delete keys
        foreach(KeyDatabaseHandler::selectBySystem($system->getId()) as $key)
            KeyOperator::delete($key);

        HistoryRecorder::writeHistory('CLIFF_System', HistoryRecorder::DELETE, $system->getId(), $system);
        return SystemDatabaseHandler::delete($system->getId());
    }

    /**
     * @param int $id
     * @return System
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function get(int $id): System
    {
        return SystemDatabaseHandler::selectById($id);
    }

    /**
     * @param string $code
     * @return System
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getByCode(string $code): System
    {
        return SystemDatabaseHandler::selectByCode($code);
    }

    /**
     * @param array $search
     * @return System[]
     * @throws \exceptions\DatabaseException
     */
    public static function getSearchResults(array $search): array
    {
        return SystemDatabaseHandler::search($search['code'], $search['name']);
    }
}
