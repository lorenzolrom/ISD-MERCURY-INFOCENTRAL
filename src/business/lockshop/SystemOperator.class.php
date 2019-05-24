<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/24/2019
 * Time: 12:23 PM
 */


namespace business\lockshop;


use business\Operator;
use database\lockshop\SystemDatabaseHandler;
use models\lockshop\System;
use utilities\HistoryRecorder;

class SystemOperator extends Operator
{
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
     * @param $vals
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function search($vals): array
    {
        return SystemDatabaseHandler::select($vals['code'], $vals['name']);
    }

    /**
     * @param array $vals
     * @return int
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\ValidationError
     * @throws \exceptions\SecurityException
     */
    public static function create(array $vals): int
    {
        self::validate('models\lockshop\System', $vals, TRUE);

        $system = SystemDatabaseHandler::insert($vals['name'], $vals['code']);
        HistoryRecorder::writeHistory('LockShop_System', HistoryRecorder::CREATE, $system->getId(), $system);

        return $system->getId();
    }

    /**
     * @param System $system
     * @param array $vals
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function update(System $system, array $vals): bool
    {
        $full = FALSE; // Perform full validation

        if(!isset($vals['code']) OR $system->getCode() != $vals['code'])
            $full = TRUE;

        self::validate('models\lockshop\System', $vals, $full);

        HistoryRecorder::writeHistory('models\lockshop\System', HistoryRecorder::MODIFY, $system->getId(), $system, $vals);
        SystemDatabaseHandler::update($system->getId(), $vals['name'], $vals['code'], $system->getParent(), $system->getMaster());

        return TRUE;
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
        HistoryRecorder::writeHistory('models\lockshop\System', HistoryRecorder::DELETE, $system->getId(), $system);
        SystemDatabaseHandler::delete($system->getId());

        return TRUE;
    }
}