<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/01/2019
 * Time: 12:01 PM
 */


namespace extensions\netuserman\business;

use business\Operator;
use exceptions\EntryNotFoundException;
use utilities\LDAPConnection;

/**
 * Interactions with LDAP
 *
 * Class NetUserOperator
 * @package business
 */
class NetUserOperator extends Operator
{
    // Settings for user accounts
    public const USER_ACCOUNT_CONTROL = array(
        'disabled' => '514',
        'enabled' => '512',
        'disabled_password_never_expires' => '66050',
        'enabled_password_never_expires' => '66048',
        '514' => 'disabled',
        '512' => 'enabled',
        '66050' => 'disabled_password_never_expires',
        '66048' => 'enabled_password_never_expires'
    );

    public const DEFAULT_ATTRIBUTES = array(
        'givenname', // First Name
        'initials', // Middle Name / Initials
        'sn', // Last Name
        'cn', // Common Name
        'distinguishedname',
        'userprincipalname', // Login Name
        'displayname', // Display Name
        'name', // Full Name
        'description', // Description
        'physicaldeliveryofficename', // Office
        'telephonenumber', // Telephone Number
        'mail', // Email
        'memberof', // Add to Groups
        'accountexpires', // Account Expires (use same date format as server)
        'title', // Title
        'removememberof', // Remove group membership,
        'useraccountcontrol' // Disable and password expire status
    );

    public const SEARCH_ATTRIBUTES = array(
        'samaccountname',
        'givenname',
        'sn'
    );

    /**
     * @param string $username
     * @param array $attributes
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\LDAPException
     */
    public static function getUserDetails(string $username, array $attributes = self::DEFAULT_ATTRIBUTES): array
    {
        $ldap = new LDAPConnection();
        $ldap->bind();

        $results = $ldap->searchByUsername($username, $attributes);

        if($results['count'] !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        $results = $results[0];

        /*
         * Format data
         *
         * Array (
         *  [attrName] => Array(
         *      [count] => numResults
         *      [index] => value
         *  )
         * )
         */

        $formatted = array();

        foreach(array_keys($results) as $attrName)
        {
            if(is_numeric($attrName)) // Skip numbered indexes
                continue;

            if(!isset($results[$attrName]['count'])) // Skip fields with no counts
                continue;

            // Single result
            if($results[$attrName]['count'] == 1 AND $attrName != 'memberof')
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
        }

        foreach(self::DEFAULT_ATTRIBUTES as $key)
        {
            $key = strtolower($key);

            if(!isset($formatted[$key]))
                $formatted[$key] = '';
        }

        // Check for user account control
        if(isset($formatted['useraccountcontrol']))
        {
            if(!in_array($formatted['useraccountcontrol'], self::USER_ACCOUNT_CONTROL)) // If not valid, ignore
                unset($formatted['useraccountcontrol']);
            else // Translate to code
                $formatted['useraccountcontrol'] = self::USER_ACCOUNT_CONTROL[$formatted['useraccountcontrol']];
        }

        return $formatted;
    }

    /**
     * @param string $username
     * @param array $vals
     * @return bool
     * @throws \exceptions\LDAPException
     */
    public static function updateUser(string $username, array $vals): bool
    {
        foreach(array_keys($vals) as $attr)
        {
            // Remove non-allowed attributes
            if(!in_array($attr, self::DEFAULT_ATTRIBUTES))
                unset($vals[$attr]);
            else if(strlen($vals[$attr]) === 0) // Blank attributes must be empty arrays
                $vals[$attr] = array();
        }

        // Check for user account control
        if(isset($vals['useraccountcontrol']))
        {
            if(!in_array($vals['useraccountcontrol'], self::USER_ACCOUNT_CONTROL)) // If not valid, ignore
                unset($vals['useraccountcontrol']);
            else // Translate to code
                $vals['useraccountcontrol'] = self::USER_ACCOUNT_CONTROL[$vals['useraccountcontrol']];
        }

        $ldap = new LDAPConnection();
        $ldap->bind();
        return $ldap->updateLDAPEntry($username, $vals);
    }

    /**
     * @param $filterAttrs
     * @return array
     * @throws \exceptions\LDAPException
     */
    public static function searchUsers($filterAttrs): array
    {
        $ldap = new LDAPConnection();
        $ldap->bind();

        return $ldap->searchUsers($filterAttrs, array('userprincipalname', 'sn', 'givenname'));
    }
}