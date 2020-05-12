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
use extensions\cliff\database\CoreDatabaseHandler;
use extensions\cliff\models\Core;
use utilities\HistoryRecorder;

class CoreOperator extends Operator
{
    /**
     * @param array $vals // with indexes systemCode, stamp, type, keyway, notes
     * @return Core[]
     * @throws \exceptions\DatabaseException
     */
    public static function getSearchResults(array $vals): array
    {
        return CoreDatabaseHandler::search($vals['systemCode'], $vals['stamp'], $vals['type'], $vals['keyway'], $vals['notes']);
    }

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
     * @param array $vals // with indexes systemCode, stamp, pinData, type, keyway, notes
     * @return array // An array containing the new Core ID
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
        if(CoreDatabaseHandler::stampInUse($system->getId(), (string)$vals['stamp']))
            throw new ValidationError(array('Stamp already in use in system'));

        // Auto-Class Validation
        self::validate('extensions\cliff\models\Core', $vals);

        // Create record
        $core = CoreDatabaseHandler::insert($system->getId(), $vals['stamp'], $vals['pinData'], $vals['type'], $vals['keyway'], $vals['notes']);

        // Create history
        HistoryRecorder::writeHistory('CLIFF_Core', HistoryRecorder::CREATE, $core->getId(), $core);

        return array('id' => $core->getId());
    }

    /**
     * @param Core $core
     * @param array $vals
     * @return bool
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function update(Core $core, array $vals): bool
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
        if($core->getSystem() !== $system->getId() OR $core->getStamp() !== $vals['stamp'])
            if(CoreDatabaseHandler::stampInUse($system->getId(), $vals['stamp']))
                throw new ValidationError(array('Stamp already in use in system'));

        // Auto-Class Validation
        self::validate('extensions\cliff\models\Core', $vals);

        // Replace systemCode with system (ID) in vals for history, this is what gets put into the database
        unset($vals['systemCode']);
        $vals['system'] = $system->getId();

        // Create history
        HistoryRecorder::writeHistory('CLIFF_Core', HistoryRecorder::MODIFY, $core->getId(), $core, $vals);

        // Update record
        return CoreDatabaseHandler::update($core->getId(), $vals['system'], $vals['stamp'], $vals['pinData'], $vals['type'], $vals['keyway'], $vals['notes']);
    }

    /**
     * @param Core $core
     * @return bool
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function delete(Core $core): bool
    {
        HistoryRecorder::writeHistory('CLIFF_Core', HistoryRecorder::DELETE, $core->getId(), $core);
        return CoreDatabaseHandler::delete($core->getId());
    }
}