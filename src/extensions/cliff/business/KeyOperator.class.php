<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/11/2020
 * Time: 2:16 PM
 */


namespace extensions\cliff\business;


use business\Operator;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use extensions\cliff\database\KeyDatabaseHandler;
use extensions\cliff\models\Key;
use utilities\HistoryRecorder;

class KeyOperator extends Operator
{
    /**
     * @param array $vals // with indexes systemCode, stamp, bitting, type, keyway, notes
     * @return Key[]
     * @throws \exceptions\DatabaseException
     */
    public static function getSearchResults(array $vals): array
    {
        return KeyDatabaseHandler::search($vals['systemCode'], $vals['stamp'], $vals['bitting'], $vals['type'], $vals['keyway'], $vals['notes']);
    }

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
     * @param array $vals // with indexes systemCode, stamp, bitting, type, keyway, notes
     * @return array // An array containing the new Key ID
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function create(array $vals): array
    {
        // Validate system
        $system = NULL;

        try
        {
            $system = SystemOperator::getByCode((string)$vals['systemCode']);
        }
        catch(EntryNotFoundException $e)
        {
            throw new ValidationError(array('System not specified, or not valid'));
        }

        // Validate stamp is not in use for system
        if(KeyDatabaseHandler::stampInUse($system->getId(), (string)$vals['stamp']))
            throw new ValidationError(array('Stamp already in use in system'));

        // Auto-Class Validation
        self::validate('extensions\cliff\models\Key', $vals);

        // Create record
        $key = KeyDatabaseHandler::insert($system->getId(), $vals['stamp'], $vals['bitting'], $vals['type'], $vals['keyway'], $vals['notes']);

        // Create history
        HistoryRecorder::writeHistory('CLIFF_Key', HistoryRecorder::CREATE, $key->getId(), $key);

        return array('id' => $key->getId());
    }

    /**
     * @param Key $key
     * @param array $vals
     * @return bool
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function update(Key $key, array $vals): bool
    {
        // Validate system
        $system = NULL;

        try
        {
            $system = SystemOperator::getByCode($vals['systemCode']);
        }
        catch(EntryNotFoundException $e)
        {
            throw new ValidationError(array('System not specified, or not valid'));
        }

        // Validate stamp is not in use for system if either has been changed
        if($key->getSystem() !== $system->getId() OR $key->getStamp() !== $vals['stamp'])
            if(KeyDatabaseHandler::stampInUse($system->getId(), $vals['stamp']))
                throw new ValidationError(array('Stamp already in use in system'));

        // Auto-Class Validation
        self::validate('extensions\cliff\models\Key', $vals);

        // Replace systemCode with system (ID) in vals for history, this is what gets put into the database
        unset($vals['systemCode']);
        $vals['system'] = $system->getId();

        // Create history
        HistoryRecorder::writeHistory('CLIFF_Key', HistoryRecorder::MODIFY, $key->getId(), $key, $vals);

        // Update record
        return KeyDatabaseHandler::update($key->getId(), $vals['system'], $vals['stamp'], $vals['bitting'], $vals['type'], $vals['keyway'], $vals['notes']);
    }

    /**
     * @param Key $key
     * @return bool
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function delete(Key $key): bool
    {
        HistoryRecorder::writeHistory('CLIFF_Key', HistoryRecorder::DELETE, $key->getId(), $key);
        return KeyDatabaseHandler::delete($key->getId());
    }
}