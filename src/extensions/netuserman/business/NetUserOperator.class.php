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
use exceptions\LDAPException;
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
     * @throws LDAPException
     * @throws ValidationError
     */
    public static function updateUser(string $username, array $vals): bool
    {
        foreach(array_keys($vals) as $attr)
        {
            // Remove non-allowed attributes
            if(!in_array($attr, ExtConfig::OPTIONS['usedAttributes']))
                unset($vals[$attr]);
            else if(!is_array($vals[$attr]) AND strlen($vals[$attr]) === 0) // Blank attributes must be empty arrays
                $vals[$attr] = array();
        }

        // Translate useraccountcontrol, if present
        if(isset($vals['useraccountcontrol']) AND is_array($vals['useraccountcontrol']))
        {
            $vals['useraccountcontrol'] = self::flagsToUAC($vals['useraccountcontrol']);
        }

        // Ensure that the domain suffix is appended to the userprincipalname
        if(isset($vals['userprincipalname']))
        {
            // Remove domain '@' suffix from userprincipalname, if present
            $vals['userprincipalname'] = explode(\Config::OPTIONS['ldapPrincipalSuffix'], $vals['userprincipalname'])[0];

            try
            {
                // Check if username already exists
                self::getUserDetails($vals['userprincipalname'], array('userprincipalname'));
                throw new ValidationError(array('Login name already in use'));
            }
            catch(EntryNotFoundException $e){} // Do nothing

            // Re-add domain '@'
            $vals['userprincipalname'] = $vals['userprincipalname'] . \Config::OPTIONS['ldapPrincipalSuffix'];
        }

        $ldap = new LDAPConnection();
        $ldap->bind();

        return $ldap->updateUser($username, $vals);
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

            foreach(ExtConfig::OPTIONS['returnedSearchAttributes'] as $attr)
            {
                if(!isset($user[$attr])) // Fill in the blanks
                    $user[$attr] = '';
            }

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

        return $ldap->updateUser($username, array('thumbnailphoto' => $imageContents));
    }

    /**
     * @param string $username
     * @param array $args // 'password' and 'confirm'
     * @return bool
     * @throws LDAPException
     * @throws ValidationError
     */
    public static function resetPassword(string $username, array $args): bool
    {
        $password = (string)$args['password'];
        $confirm = (string)$args['confirm'];

        if($password != $confirm)
            throw new ValidationError(array('Passwords do not match'));

        $ldap = new LDAPConnection();
        $ldap->bind();

        return $ldap->setPassword($username, $password);
    }

    /**
     * @param string $username
     * @param array $vals
     * @return bool
     * @throws EntryNotFoundException
     * @throws LDAPException
     */
    public static function modifyGroups(string $username, array $vals): bool
    {
        $userDN = self::getUserDetails($username, array('distinguishedname'))['distinguishedname'];

        $addGroups = is_array($vals['addGroups']) ? $vals['addGroups'] : array();
        $removeGroups = is_array($vals['removeGroups']) ? $vals['removeGroups'] : array();

        $addDNs = array();
        $removeDNs = array();

        foreach($addGroups as $group)
        {
            try
            {
                $addDNs[] = NetGroupOperator::getGroupDetails($group, array('distinguishedname'))['distinguishedname'];
            }
            catch (EntryNotFoundException $e)
            {
                // Ignore
            }
        }

        foreach($removeGroups as $group)
        {
            try
            {
                $removeDNs[] = NetGroupOperator::getGroupDetails($group, array('distinguishedname'))['distinguishedname'];
            }
            catch(EntryNotFoundException $e)
            {
                // Ignore
            }
        }

        $ldap = new LDAPConnection();
        $ldap->bind();

        foreach($addDNs as $dn)
        {
            $ldap->addAttribute($dn, 'member', $userDN);
        }

        foreach($removeDNs as $dn)
        {
            $ldap->delAttribute($dn, 'member', $userDN);
        }

        return TRUE;
    }

    /**
     * @param string $username
     * @return bool
     * @throws EntryNotFoundException
     * @throws LDAPException
     */
    public static function deleteUser(string $username): bool
    {
        $userDN = self::getUserDetails($username, array('distinguishedname'))['distinguishedname'];
        $ldap = new LDAPConnection();
        $ldap->bind();

        return $ldap->deleteObject($userDN);
    }

    /**
     * @param array $attrs
     * @return array|null Array with new user login name if created, NULL if failed
     * @throws LDAPException
     * @throws ValidationError
     */
    public static function createUser(array $attrs): ?array
    {
        // Extract passwords
        $password = isset($attrs['password']) ? $attrs['password'] : '';
        $confirm = isset($attrs['confirm']) ? $attrs['confirm'] : '';

        // Remove invalid attributes
        foreach(array_keys($attrs) as $attr)
        {
            if(!in_array($attr, ExtConfig::OPTIONS['usedAttributes']))
                unset($attrs[$attr]);
        }

        if(isset($attrs['cn'])) // CN is not set this way, will use DN
            unset($attrs['cn']);

        $errors = array();

        // Remove domain '@' suffix from userprincipalname, if present
        $attrs['userprincipalname'] = explode(\Config::OPTIONS['ldapPrincipalSuffix'], $attrs['userprincipalname'])[0];
        $attrs['samaccountname'] = $attrs['userprincipalname'];

        // Check DN is present
        if(strlen($attrs['distinguishedname']) < 1)
            $errors[] = 'Distinguished Name (DN) is required';

        // Check username is present
        if(strlen($attrs['userprincipalname']) < 1)
            $errors[] = 'User Principal Name (Login) is required';

        // Check for existing user with username
        try
        {
            self::getUserDetails($attrs['userprincipalname'], array('userprincipalname'));
            $errors[] = 'Login name already in use';
        }
        catch(EntryNotFoundException $e){} // Do nothing, userprincipalname does not exist

        // Re-add domain '@'
        $attrs['userprincipalname'] = $attrs['userprincipalname'] . \Config::OPTIONS['ldapPrincipalSuffix'];

        // Make sure passwords match
        if($password !== $confirm)
            $errors[] = 'Passwords do not match';

        // Throw errors, if present
        if(!empty($errors))
            throw new ValidationError($errors);

        // Add objectclass "person"
        $attrs['objectclass'] = array('top', 'person', 'organizationalPerson', 'user');

        $ldap = new LDAPConnection();
        $ldap->bind();

        // Extract and unset DN
        $dn = $attrs['distinguishedname'];

        // Translate useraccountcontrol
        if(isset($attrs['useraccountcontrol']) AND is_array($attrs['useraccountcontrol']))
        {
            $attrs['useraccountcontrol'] = self::flagsToUAC($attrs['useraccountcontrol']);
        }

        $attrs['unicodePwd'] = LDAPConnection::getLDAPFormattedPassword($password);

        // Remove empty attributes
        foreach(array_keys($attrs) as $attr)
        {
            if((is_array($attrs[$attr]) AND empty($attrs[$attr])) OR (!is_array($attrs[$attr]) AND strlen($attrs[$attr]) === 0))
                unset($attrs[$attr]);
        }

        // Create object
        if($ldap->createObject($dn, $attrs))
            return array('userprincipalname' => $attrs['userprincipalname']);


        return NULL;
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