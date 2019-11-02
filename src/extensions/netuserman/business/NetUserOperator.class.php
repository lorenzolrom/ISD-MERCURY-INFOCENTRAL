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
use exceptions\ValidationError;
use extensions\netuserman\ExtConfig;
use utilities\LDAPConnection;

/**
 * Interactions with LDAP
 *
 * Class NetUserOperator
 * @package business
 */
class NetUserOperator extends Operator
{
    // These are two-way translations for useraccountcontrol codes
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

    public const UAC_FORWARD_LOOKUP = array(
        'SCRIPT' => 1, // Running the login script
        'ACCOUNTDISABLE' => 2, // The account id disabled
        'HOMEDIR_REQUIRED' => 8, // The home folder is required
        'LOCKOUT' => 16, // The account is locked
        'PASSWD_NOTREQD' => 32, // No password required
        'PASSWD_CANT_CHANGE' => 64, // Prevent user from changing password
        'ENCRYPTED_TEXT_PWD_ALLOWED' => 128, // Store password using reversible encryption
        'TEMP_DUPLICATE_ACCOUNT' => 256,
        'NORMAL_ACCOUNT' => 512, // A default account, active
        'INTERDOMAIN_TRUST_ACCOUNT' => 2048,
        'WORKSTATION_TRUST_ACCOUNT' => 4096,
        'SERVER_TRUST_ACCOUNT' => 8192,
        'DONT_EXPIRE_PASSWORD' => 65536,
        'MSN_LOGON_ACCOUNT' => 131072,
        'SMARTCARD_REQUIRED' => 262144,
        'TRUSTED_FOR_DELEGATION' => 524288,
        'NOT_DELEGATED' => 1048576,
        'USE_DES_KEY_ONLY' => 2097152,
        'DONT_REQ_PREAUTH' => 4194304, // Kerberos pre-authentication is not required
        'PASSWORD_EXPIRED'=> 8388608, // The user password has expired
        'TRUSTED_TO_AUTH_FOR_DELEGATION' => 16777216,
        'PARTIAL_SECRETS_ACCOUNT' => 67108864
    );

    public const UAC_REVERSE_LOOKUP = array(
        67108864 => 'PARTIAL_SECRETS_ACCOUNT',
        16777216 => 'TRUSTED_TO_AUTH_FOR_DELEGATION',
        8388608 => 'PASSWORD_EXPIRED',
        4194304 => 'DONT_REQ_PREAUTH',
        2097152 => 'USE_DES_KEY_ONLY',
        1048576 => 'NOT_DELEGATED',
        524288 => 'TRUSTED_FOR_DELEGATION',
        262144 => 'SMARTCARD_REQUIRED',
        131072 => 'MSN_LOGON_ACCOUNT',
        65536 => 'DONT_EXPIRE_PASSWORD',
        8192 => 'SERVER_TRUST_ACCOUNT',
        4096 => 'WORKSTATION_TRUST_ACCOUNT',
        2048 => 'INTERDOMAIN_TRUST_ACCOUNT',
        512 => 'NORMAL_ACCOUNT',
        256 => 'TEMP_DUPLICATE_ACCOUNT',
        128 => 'ENCRYPTED_TEXT_PWD_ALLOWED',
        64 => 'PASSWD_CANT_CHANGE',
        32 => 'PASSWD_NOTREQD',
        16 => 'LOCKOUT',
        8 => 'HOMEDIR_REQUIRED',
        2 => 'ACCOUNTDISABLE',
        1 => 'SCRIPT'
    );

    /**
     * @param string $username
     * @param array $attributes
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\LDAPException
     */
    public static function getUserDetails(string $username, array $attributes = ExtConfig::OPTIONS['usedAttributes']): array
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

        // Loop through returned values
        foreach(array_keys($results) as $attrName)
        {
            if(is_numeric($attrName)) // Skip numbered indexes
                continue;

            if(!isset($results[$attrName]['count'])) // Skip fields with no counts
                continue;

            // Single result
            if($results[$attrName]['count'] == 1 AND $attrName != 'memberof') // This ignores memberof, which should be treated as an array even if it only has one value
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

        // Insert blank records if the attribute was requested but not returned by LDAP
        foreach($attributes as $key)
        {
            $key = strtolower($key);

            if(!isset($formatted[$key]))
                $formatted[$key] = '';
        }

        // Check for user account control, if it exists convert it to an array of flags
        if(isset($formatted['useraccountcontrol']))
        {
            $formatted['useraccountcontrol'] = self::getUACFlags((int)$formatted['useraccountcontrol']);
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
            if(!in_array($attr, ExtConfig::OPTIONS['usedAttributes']))
                unset($vals[$attr]);
            else if(strlen($vals[$attr]) === 0) // Blank attributes must be empty arrays
                $vals[$attr] = array();
        }

        // Translate useraccountcontrol, if present
        if(isset($vals['useraccountcontrol']) AND is_array($vals['useraccountcontrol']))
        {
            $vals['useraccountcontrol'] = self::flagsToUAC($vals['useraccountcontrol']);
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

        $results = $ldap->searchUsers($filterAttrs, ExtConfig::OPTIONS['returnedSearchAttributes']);

        $users = array();

        for($i = 0; $i < $results['count']; $i++)
        {
            $user = array();

            foreach(array_keys($results[$i]) as $attr)
            {
                if(is_numeric($attr)) // Skip integer indexes
                    continue;

                if(is_array($results[$i][$attr])) // Attribute has details
                {
                    if((int)$results[$i][$attr]['count'] == 1) // Only one detail in this attribute
                        $user[$attr] = $results[$i][$attr][0];
                    else // Many details in this attribute
                    {
                        $subData = array();
                        for($j = 0; $j < (int)$results[$i][$attr]['count']; $j++)
                        {
                            $subData[] = $results[$i][$attr][$j];
                        }

                        $user[$attr] = $subData;
                    }
                }
                else
                {
                    $user[$attr] = ''; // No attribute data, leave blank
                }
            }

            if(isset($user['useraccountcontrol']))
                $user['useraccountcontrol'] = self::getUACFlags((int)$user['useraccountcontrol']);

            $users[] = $user;
        }

        return $users;
    }

    /**
     * @param string $username
     * @param string $imageContents
     * @return bool
     * @throws ValidationError
     * @throws \exceptions\LDAPException
     */
    public static function updateUserImage(string $username, string $imageContents): bool
    {
        $errors = array();

        // Check image length
        if(strlen($imageContents) === 0)
            $errors[] = 'Photo required';

        // Check image type
        if(strtolower($_FILES['thumbnailphoto']['type']) !== 'image/jpeg')
            $errors[] = 'Photo must be a JPEG';

        if(!empty($errors))
            throw new ValidationError($errors);

        // Change photo
        $ldap = new LDAPConnection();
        $ldap->bind();

        return $ldap->updateLDAPEntry($username, array('thumbnailphoto' => $imageContents));
    }

    /**
     * Converts a useraccountcontrol integer into an array of flag names
     * @param int $useraccountcontrol
     * @return array
     */
    private static function getUACFlags(int $useraccountcontrol): array
    {
        $attributes = array();

        while($useraccountcontrol > 0)
        {
            foreach(self::UAC_REVERSE_LOOKUP as $flag => $flagName)
            {
                $tmp = $useraccountcontrol - $flag; // Temporary store difference

                if($tmp > 0)
                {
                    $attributes[] = $flagName;
                    $useraccountcontrol = $tmp;
                }

                if($tmp == 0)
                {
                    if (isset(self::UAC_REVERSE_LOOKUP[$useraccountcontrol]))
                    {
                        $attributes[] = self::UAC_REVERSE_LOOKUP[$useraccountcontrol];
                    }

                    $useraccountcontrol = $tmp;
                }
            }
        }

        return $attributes;
    }

    /**
     * Converts an array of flags into a useraccountcontrol integer
     * @param array $flags
     * @return int
     */
    private static function flagsToUAC(array $flags): int
    {
        $uac = 0;

        foreach($flags as $flag)
        {
            if(in_array($flag, array_keys(self::UAC_FORWARD_LOOKUP)))
                $uac += (int)self::UAC_FORWARD_LOOKUP[$flag];
        }

        return $uac;
    }
}