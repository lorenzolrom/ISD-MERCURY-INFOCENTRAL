<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/24/2019
 * Time: 2:51 PM
 */


namespace business\lockshop;


use business\Operator;
use database\lockshop\CoreDatabaseHandler;
use exceptions\ValidationError;
use exceptions\ValidationException;
use models\lockshop\Core;
use models\lockshop\System;
use utilities\HistoryRecorder;

class CoreOperator extends Operator
{
    /**
     * @param int $id
     * @return Core
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function get(int $id): Core
    {
        return CoreDatabaseHandler::selectById($id);
    }

    /**
     * @param System $sys
     * @param array $vals
     * @return int
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function create(System $sys, array $vals): int
    {
        self::validate('models\lockshop\Core', $vals);

        $vals['system'] = $sys->getId();

        try
        {
            Core::__validateCode($sys->getId(), $vals['code']);
        }
        catch(ValidationException $e)
        {
            throw new ValidationError(array($e));
        }

        $core = CoreDatabaseHandler::insert($sys->getId(), $vals['code'], $vals['quantity']);
        HistoryRecorder::writeHistory('LockShop_Core', HistoryRecorder::CREATE, $core->getId(), $core);

        return $core->getId();
    }

    /**
     * @param Core $core
     * @param array $vals
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function update(Core $core, array $vals): bool
    {
        if(!isset($vals['code']) OR $core->getCode() != $vals['code'])
        {
            try{Core::__validateCode($core->getSystem(), $vals['code']);}
            catch(ValidationException $e){throw new ValidationError(array($e));}
        }

        self::validate('models\lockshop\Core', $vals);

        HistoryRecorder::writeHistory('LockShop_Core', HistoryRecorder::MODIFY, $core->getId(), $core, $vals);
        CoreDatabaseHandler::update($core->getId(), $vals['code'], $vals['quantity']);

        return TRUE;
    }

    /**
     * @param Core $core
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function delete(Core $core): bool
    {
        HistoryRecorder::writeHistory('LockShop_Core', HistoryRecorder::DELETE, $core->getId(), $core);
        return CoreDatabaseHandler::delete($core->getId());
    }
}