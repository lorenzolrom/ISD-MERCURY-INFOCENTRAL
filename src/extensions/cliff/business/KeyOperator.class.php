<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
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
use extensions\cliff\database\KeyIssueDatabaseHandler;
use extensions\cliff\models\Key;
use extensions\cliff\models\KeyIssue;
use extensions\cliff\utilities\KeySequencer;
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
        foreach(KeyOperator::getKeyIssues($key) as $issue)
            self::deleteIssue($issue);

        HistoryRecorder::writeHistory('CLIFF_Key', HistoryRecorder::DELETE, $key->getId(), $key);
        return KeyDatabaseHandler::delete($key->getId());
    }

    /**
     * @param array $vals
     * @return int
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function sequenceKeys(array $vals): int
    {
        $ks = new KeySequencer();
        return $ks->sequenceKeys((string)$vals['systemCode'], (string)$vals['stamp'], (string)$vals['type'], (string)$vals['keyway'], (string)$vals['c1'], (string)$vals['c2'], (string)$vals['c3'], (string)$vals['c4'], (string)$vals['c5'], (string)$vals['c6'], (string)$vals['c7'], (int)$vals['seqStart'], (int)$vals['seqEnd'], (int)$vals['padding']);
    }

    /**
     * @param Key $key
     * @param array $vals
     * @return KeyIssue
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws ValidationError
     */
    public static function issueKey(Key $key, array $vals): KeyIssue
    {
        $errs = array();

        // Validate serial not in use
        if(!ctype_digit($vals['serial']))
            $errs[] = "Serial must be an integer";
        else if(KeyIssueDatabaseHandler::serialInUse($key->getId(), (int)$vals['serial']))
            $errs[] = "Serial is in use";

        if(strlen((string)$vals['issuedTo']) < 1)
            $errs[] = "Issued-to is required";

        if(!empty($errs))
            throw new ValidationError($errs);

        $issue = KeyIssueDatabaseHandler::insert($key->getId(), (int)$vals['serial'], (string)$vals['issuedTo']);
        HistoryRecorder::writeHistory('CLIFF_KeyIssue', HistoryRecorder::CREATE, $issue->getId(), $issue);

        return $issue;
    }

    /**
     * @param Key $key
     * @return Key[]
     * @throws \exceptions\DatabaseException
     */
    public static function getKeyIssues(Key $key): array
    {
        return KeyIssueDatabaseHandler::selectByKey($key->getId());
    }

    /**
     * @param int $id
     * @return KeyIssue
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function getKeyIssue(int $id): KeyIssue
    {
        return KeyIssueDatabaseHandler::selectById($id);
    }

    /**
     * @param KeyIssue $issue
     * @return bool
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function deleteIssue(KeyIssue $issue): bool
    {
        HistoryRecorder::writeHistory('CLIFF_KeyIssue', HistoryRecorder::DELETE, $issue->getId(), $issue);
        return KeyIssueDatabaseHandler::delete($issue->getId());
    }

    /**
     * @param KeyIssue $issue
     * @param string $issuedTo
     * @return bool
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function updateIssue(KeyIssue $issue, string $issuedTo): bool
    {
        HistoryRecorder::writeHistory('CLIFF_KeyIssue', HistoryRecorder::MODIFY, $issue->getId(), $issue, array('issuedTo' => $issuedTo));
        return KeyIssueDatabaseHandler::update($issue->getId(), $issuedTo);
    }
}
