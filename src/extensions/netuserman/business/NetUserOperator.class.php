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
    public const DEFAULT_ATTRIBUTES = array(
        'givenName', // First Name
        'initials', // Middle Name / Initials
        'sn', // Last Name
        'cn', // Common Name
        'userPrincipalName', // Login Name
        'sAMAccountName', // Login Name (Pre-Windows 2000)
        'displayName', // Display Name
        'name', // Full Name
        'description', // Description
        'physicalDeliveryOfficeName', // Office
        'telephoneNumber', // Telephone Number
        'mail', // Email
        'wWWHomePage', // Web Page
        'memberOf', // Add to Groups
        'accountExpires', // Account Expires (use same date format as server)
        'title', // Title
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

        return $formatted;
    }
}