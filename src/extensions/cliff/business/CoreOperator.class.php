<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
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
use extensions\cliff\database\CoreLocationDatabaseHandler;
use extensions\cliff\database\KeyDatabaseHandler;
use extensions\cliff\models\Core;
use extensions\cliff\models\CoreLocation;
use extensions\cliff\utilities\CoreBuilder;
use extensions\cliff\utilities\KeySolver;
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
        foreach(self::getLocations($core) as $location)
            self::deleteLocation($location);

        HistoryRecorder::writeHistory('CLIFF_Core', HistoryRecorder::DELETE, $core->getId(), $core);
        return CoreDatabaseHandler::delete($core->getId());
    }

    /**
     * Solves all keys that will operate the specified core.
     *
     * @param Core $core
     * @return array // A 2-D array of bitting codes, with each record also containing a KeyID if the bitting
     *      matches a key within the same system.
     * @throws \exceptions\DatabaseException
     */
    public static function readCore(Core $core): array
    {
        $bittings = array(); // Final array, holding bittings and matching key IDs
        $bittings['control'] = array();
        $bittings['operating'] = array();

        $ks = new KeySolver();

        $ksCore = $ks->getCore($core->getId());

        $controlBitting = $ks->getTipToBowString($ks->getControlBitting($ksCore));
        $controlID = KeyDatabaseHandler::selectIDBySystemBitting($core->getSystem(), $controlBitting);

        $bittings['control'] = array('bitting' => $controlBitting, 'key' => $controlID);

        $rawBittings = $ks->getWorkingKeys($ksCore);

        $rawBittingStrings = array();

        foreach($rawBittings as $bitting)
        {
            $rawBittingStrings[] = $ks->getTipToBowString($bitting);
        }

        sort($rawBittingStrings);

        foreach($rawBittingStrings as $bitting)
        {
            $key = KeyDatabaseHandler::selectIDBySystemBitting($core->getSystem(), $bitting);

            $bittings['operating'][] = array(
                'bitting' => $bitting,
                'key' => $key
            );

        }

        return $bittings;
    }

    /**
     * @param string $systemCode Code for the system containing keys, cores
     * @param string $controlStamp Stamp ONLY for the key acting as control
     * @param string $operatingStamps Stamps, separated by commas, of keys operating this core
     * @param string|null $targetCoreCode Optional, the stamp of the core this data will be applied to
     * @return array // 2-D array of pin data (NOT the formatted string)
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function buildCore(string $systemCode, string $controlStamp, string $operatingStamps, ?string $targetCoreCode = NULL): array
    {
        try
        {
            $system = SystemOperator::getByCode($systemCode);
            $control = KeyDatabaseHandler::selectBySystemStamp($system->getId(), $controlStamp);

            $operating = array();

            foreach(explode(',', $operatingStamps) as $stamp)
            {
                $operating[] = KeyDatabaseHandler::selectBySystemStamp($system->getId(), $stamp);
            }

            $cb = new CoreBuilder();
            $pinArray = $cb->buildCore($control, $operating);

            //Write to target core, if specified
            if($targetCoreCode !== NULL AND strlen($targetCoreCode) > 0)
            {
                $tempPinArray = array(); // Hold imploded rows

                foreach($pinArray as $row)
                {
                    $tempPinArray[] = implode(',', $row);
                }

                $pinData = implode('|', $tempPinArray);

                $core = CoreDatabaseHandler::selectBySystemStamp($system->getId(), $targetCoreCode);
                CoreOperator::update($core, array(
                    'systemCode' => $system->getCode(),
                    'stamp' => $core->getStamp(),
                    'pinData' => $pinData,
                    'type' => $core->getType(),
                    'keyway' => $core->getKeyway(),
                    'notes' => $core->getNotes()
                ));
            }

            return $pinArray;
        }
        catch(EntryNotFoundException $e)
        {
            throw new ValidationError(array('Control, operating, or core data is invalid'));
        }
    }

    /**
     * @param string $systemCode
     * @param string $coreStamps
     * @return array // Key bitting data
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     */
    public static function compareCores(string $systemCode, string $coreStamps): array
    {
        try
        {
            $system = SystemOperator::getByCode($systemCode);
            $coreStamps = explode(',', $coreStamps);

            $cores = array();

            foreach($coreStamps as $stamp)
            {
                $cores[] = CoreDatabaseHandler::selectBySystemStamp($system->getId(), $stamp);
            }

            $ks = new KeySolver();
            $commonKeys = $ks->getCommon($cores);

            $keys = array();

            foreach($commonKeys as $commonKey)
            {
                $key = KeyDatabaseHandler::selectIDBySystemBitting($system->getId(), $commonKey);

                $keys[] = array(
                    'bitting' => $commonKey,
                    'key' => $key
                );
            }

            return $keys;

        }
        catch(EntryNotFoundException $e)
        {
            throw new ValidationError(array('System or core stamps are invalid'));
        }
    }

    /**
     * @param Core $core
     * @param array $vals
     * @return CoreLocation
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function createLocation(Core $core, array $vals): CoreLocation
    {
        $errs = array();

        if(strlen((string)$vals['building']) < 1)
            $errs[] = "Building is required";

        if(strlen((string)$vals['location']) < 1)
            $errs[] = "Location is required";

        if(!empty($errs))
            throw new ValidationError($errs);

        $location = CoreLocationDatabaseHandler::insert($core->getId(), (string)$vals['building'], (string)$vals['location'], (string)$vals['notes']);
        HistoryRecorder::writeHistory('CLIFF_CoreLocation', HistoryRecorder::CREATE, $location->getId(), $location);

        return $location;
    }

    /**
     * @param Core $core
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function getLocations(Core $core): array
    {
        return CoreLocationDatabaseHandler::selectByCore($core->getId());
    }

    /**
     * @param int $id
     * @return CoreLocation
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function getLocation(int $id): CoreLocation
    {
        return CoreLocationDatabaseHandler::selectById($id);
    }

    /**
     * @param CoreLocation $location
     * @return bool
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function deleteLocation(CoreLocation $location): bool
    {
        HistoryRecorder::writeHistory('CLIFF_CoreLocation', HistoryRecorder::DELETE, $location->getId(), $location);
        return CoreLocationDatabaseHandler::delete($location->getId());
    }

    /**
     * @param CoreLocation $cl
     * @param array $vals
     * @return bool
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function updateLocation(CoreLocation $cl, array $vals): bool
    {
        $errs = array();

        if(strlen((string)$vals['building']) < 1)
            $errs[] = "Building is required";

        if(strlen((string)$vals['location']) < 1)
            $errs[] = "Location is required";

        if(!empty($errs))
            throw new ValidationError($errs);

        HistoryRecorder::writeHistory('CLIFF_CoreLocation', HistoryRecorder::MODIFY, $cl->getId(), $cl, $vals);
        return CoreLocationDatabaseHandler::update($cl->getId(), (string)$vals['building'], (string)$vals['location'], (string)$vals['notes']);
    }
}
