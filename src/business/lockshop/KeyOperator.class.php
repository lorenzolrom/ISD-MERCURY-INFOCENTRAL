<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/24/2019
 * Time: 2:28 PM
 */


namespace business\lockshop;


use business\Operator;
use database\lockshop\KeyDatabaseHandler;
use exceptions\ValidationError;
use exceptions\ValidationException;
use models\lockshop\Key;
use models\lockshop\System;
use utilities\HistoryRecorder;

class KeyOperator extends Operator
{
    /**
     * @param int $id
     * @return Key
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function get(int $id): Key
    {
        return KeyDatabaseHandler::selectById($id);
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
        self::validate('models\lockshop\Key', $vals);

        $vals['system'] = $sys->getId();

        try
        {
            Key::__validateCode($sys->getId(), $vals['code']);
        }
        catch(ValidationException $e)
        {
            throw new ValidationError(array($e));
        }

        $key = KeyDatabaseHandler::insert($sys->getId(), $vals['code'], $vals['bitting'], $vals['quantity']);
        HistoryRecorder::writeHistory('LockShop_Key', HistoryRecorder::CREATE, $key->getId(), $key);

        return $key->getId();
    }

    /**
     * @param Key $key
     * @param array $vals
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function update(Key $key, array $vals): bool
    {
        if(!isset($vals['code']) OR $key->getCode() != $vals['code'])
        {
            try{Key::__validateCode($key->getSystem(), $vals['code']);}
            catch(ValidationException $e){throw new ValidationError(array($e));}
        }

        self::validate('models\lockshop\Key', $vals);

        HistoryRecorder::writeHistory('LockShop_Key', HistoryRecorder::MODIFY, $key->getId(), $key, $vals);
        KeyDatabaseHandler::update($key->getId(), $vals['code'], $vals['bitting'], $vals['quantity']);

        return TRUE;
    }

    /**
     * @param Key $key
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function delete(Key $key): bool
    {
        HistoryRecorder::writeHistory('LockShop_Key', HistoryRecorder::DELETE, $key->getId(), $key);
        return KeyDatabaseHandler::delete($key->getId());
    }
}