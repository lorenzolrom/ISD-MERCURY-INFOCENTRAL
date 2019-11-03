<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/02/2019
 * Time: 2:08 PM
 */


namespace extensions\netuserman\business;


use business\Operator;
use exceptions\EntryNotFoundException;
use extensions\netuserman\ExtConfig;
use utilities\LDAPConnection;

class NetGroupOperator extends Operator
{
    /**
     * @param $filterAttrs
     * @return array
     * @throws \exceptions\LDAPException
     */
    public static function searchGroups($filterAttrs): array
    {
        $ldap = new LDAPConnection();
        $ldap->bind();

        $results = $ldap->searchGroups($filterAttrs, ExtConfig::OPTIONS['returnedSearchGroupAttributes']);

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

            foreach(ExtConfig::OPTIONS['returnedSearchGroupAttributes'] as $attr)
            {
                if(!isset($group[$attr])) // Fill in the blanks
                    $group[$attr] = '';
            }

            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * @param string $cn
     * @param array $attributes
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\LDAPException
     */
    public static function getGroupDetails(string $cn, array $attributes = ExtConfig::OPTIONS['usedGroupAttributes']): array
    {
        // Decode URI characters
        $cn = urldecode($cn);

        $ldap = new LDAPConnection();
        $ldap->bind();

        $results = $ldap->getGroup($cn, $attributes);

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
            foreach($attributes as $key)
            {
                $key = strtolower($key);

                if(!isset($formatted[$key]))
                    $formatted[$key] = '';
            }
        }

        return $formatted;
    }

    /**
     * @param string $cn
     * @param array $vals
     * @return bool
     * @throws \exceptions\LDAPException
     */
    public static function updateGroup(string $cn, array $vals): bool
    {
        foreach(array_keys($vals) as $attr)
        {
            // Remove non-allowed attributes
            if(!in_array($attr, ExtConfig::OPTIONS['usedGroupAttributes']))
                unset($vals[$attr]);
            else if(!is_array($vals[$attr]) AND strlen($vals[$attr]) === 0) // Blank attributes must be empty arrays
                $vals[$attr] = array();
        }

        $ldap = new LDAPConnection();
        $ldap->bind();

        $dn = $ldap->getGroup($cn, array('distinguishedname'))[0]['distinguishedname'][0];

        if(isset($vals['member'])) // Member update not allowed like this
            unset($vals['member']);

        if(isset($vals['cn'])) // Name update not allowed like this, must edit distinguished name
            unset($vals['cn']);

        return $ldap->updateEntry($dn, $vals);
    }
}