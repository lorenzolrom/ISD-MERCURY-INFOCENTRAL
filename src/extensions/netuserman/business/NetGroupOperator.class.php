<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 11/02/2019
 * Time: 2:08 PM
 */


namespace extensions\netuserman\business;


use business\Operator;
use exceptions\EntryNotFoundException;
use exceptions\LDAPException;
use exceptions\ValidationError;
use extensions\netuserman\ExtConfig;
use extensions\netuserman\models\NetModel;
use utilities\HistoryRecorder;
use utilities\LDAPConnection;
use utilities\LDAPUtility;

class NetGroupOperator extends Operator
{
    /**
     * @param array $filterAttrs
     * @param array $getAttrs
     * @return array
     * @throws LDAPException
     */
    public static function searchGroups(array $filterAttrs, array $getAttrs = ExtConfig::OPTIONS['groupReturnedSearchAttributes']): array
    {
        $c = new LDAPConnection(TRUE, TRUE);

        $results = LDAPUtility::getObjects($c, $filterAttrs, $getAttrs, array('objectClass' => 'group'));

        $groups = array();

        for($i = 0; $i < $results['count']; $i++)
        {
            $group = array();

            foreach(array_keys($results[$i]) as $attr)
            {
                if(is_numeric($attr)) // Skip integer indexes
                    continue;

                if(is_array($results[$i][$attr])) // Attribute has details
                {
                    if((int)$results[$i][$attr]['count'] == 1) // Only one detail in this attribute
                        $group[$attr] = $results[$i][$attr][0];
                    else // Many details in this attribute
                    {
                        $subData = array();
                        for($j = 0; $j < (int)$results[$i][$attr]['count']; $j++)
                        {
                            $subData[] = $results[$i][$attr][$j];
                        }

                        $group[$attr] = $subData;
                    }
                }
                else
                {
                    $group[$attr] = ''; // No attribute data, leave blank
                }
            }

            foreach($getAttrs as $attr)
            {
                if(!isset($group[$attr])) // Fill in the blanks
                    $group[$attr] = '';
            }

            $groups[] = $group;
        }

        $c->close();
        return $groups;
    }

    /**
     * @param string $cn
     * @param array $getAttrs
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\LDAPException
     */
    public static function getGroupDetails(string $cn, array $getAttrs = ExtConfig::OPTIONS['groupReturnedAttributes']): array
    {
        // Decode URI characters
        $cn = urldecode($cn);

        $c = new LDAPConnection(TRUE, TRUE);

        $results = LDAPUtility::getObject($c, $cn, $getAttrs);

        if($results['count'] !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        $results = $results[0];

        $formatted = array();

        foreach(array_keys($results) as $attrName)
        {
            if(is_numeric($attrName))
                continue;

            if(!isset($results[$attrName]['count'])) // Skip fields with no counts
                continue;

            // Single result
            if($results[$attrName]['count'] == 1 AND $attrName != 'member') // This ignores member, which should be treated as an array even if it only has one value
                $formatted[$attrName] = $results[$attrName][0];
            else // Array of values
            {
                $attrArray = array();

                for($i = 0; $i < (int)$results[$attrName]['count']; $i++)
                {
                    $attrArray[] = $results[$attrName][$i];
                }

                $formatted[$attrName] = $attrArray;
            }

            // Insert blank records if the attribute was requested but not returned by LDAP
            foreach($getAttrs as $key)
            {
                $key = strtolower($key);

                if(!isset($formatted[$key]))
                    $formatted[$key] = '';
            }
        }

        $c->close();
        return $formatted;
    }

    /**
     * @param string $cn
     * @param array $vals
     * @return bool
     * @throws EntryNotFoundException
     * @throws LDAPException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function updateGroup(string $cn, array $vals): bool
    {
        foreach(array_keys($vals) as $attr)
        {
            // Remove non-allowed attributes
            if(!is_array($vals[$attr]) AND strlen($vals[$attr]) === 0) // Blank attributes must be empty arrays
                $vals[$attr] = array();
        }

        $c = new LDAPConnection(TRUE, TRUE);

        $details = self::getGroupDetails($cn, array('distinguishedname', 'objectguid'));
        $dn = $details['distinguishedname'];
        $guid = $details['objectguid'];

        $hist = HistoryRecorder::writeHistory('!NETGROUP', HistoryRecorder::MODIFY, $guid, new NetModel());

        // Format VALS for History Entry
        $histAttrs = array();

        foreach(array_keys($vals) as $attr)
        {
            $histAttrs[$attr] = array($vals[$attr]);
        }

        HistoryRecorder::writeAssocHistory($hist, $histAttrs);

        $res = LDAPUtility::updateEntry($c, $dn, $vals);

        $c->close();
        return $res;
    }

    /**
     * @param string $cn
     * @return bool
     * @throws EntryNotFoundException
     * @throws LDAPException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function deleteGroup(string $cn): bool
    {
        $details = self::getGroupDetails($cn, array('objectguid', 'distinguishedname'));
        $groupDN = $details['distinguishedname'];
        $groupGUID = $details['objectguid'];

        $c = new LDAPConnection(TRUE, TRUE);

        HistoryRecorder::writeHistory('!NETGROUP', HistoryRecorder::DELETE, $groupGUID, new NetModel());

        $res = LDAPUtility::deleteObject($c, $groupDN);

        $c->close();
        return $res;
    }

    /**
     * @param array $attrs
     * @return array|null
     * @throws EntryNotFoundException
     * @throws LDAPException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function createGroup(array $attrs): ?array
    {
        $errors = array();

        // Check DN is present
        if(strlen($attrs['distinguishedname']) < 1)
            $errors[] = 'Distinguished Name (DN) is required';

        // Throw errors, if present
        if(!empty($errors))
            throw new ValidationError($errors);

        // Remove empty attributes
        foreach(array_keys($attrs) as $attr)
        {
            if((is_array($attrs[$attr]) AND empty($attrs[$attr])) OR (!is_array($attrs[$attr]) AND strlen($attrs[$attr]) === 0))
                unset($attrs[$attr]);
        }

        $attrs['objectclass'] = array('top', 'group');

        // Create object
        $c = new LDAPConnection(TRUE, TRUE);

        if(LDAPUtility::createObject($c, $attrs['distinguishedname'], $attrs))
        {
            $cn = explode(',', $attrs['distinguishedname'])[0];
            $cn = explode('=', $cn)[1];

            $groupGUID = self::getGroupDetails($cn, array('objectguid'))['objectguid'];

            $hist = HistoryRecorder::writeHistory('!NETGROUP', HistoryRecorder::CREATE, $groupGUID, new NetModel());

            // Format VALS for History Entry
            $histAttrs = array();

            foreach(array_keys($attrs) as $attr)
            {
                if(!is_array($attrs[$attr]))
                    $histAttrs[$attr] = array($attrs[$attr]);
                else
                    $histAttrs[$attr] = $attrs[$attr];
            }

            HistoryRecorder::writeAssocHistory($hist, $histAttrs);

            $c->close();
            return array('cn' => $cn);
        }

        $c->close();
        return NULL;
    }
}
