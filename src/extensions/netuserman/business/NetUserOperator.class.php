<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
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
use extensions\netuserman\models\NetModel;
use utilities\HistoryRecorder;
use utilities\LDAPConnection;
use utilities\LDAPUtility;

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
     * @param string $cn
     * @param array $attributes
     * @return array
     * @throws EntryNotFoundException
     * @throws LDAPException
     */
    public static function getUserDetails(string $cn, array $attributes = ExtConfig::OPTIONS['userReturnedAttributes']): array
    {
        $c = new LDAPConnection(TRUE, TRUE);

        $results = LDAPUtility::getObject($c, $cn, $attributes);

        $c->close();

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

        if(isset($formatted['lastlogon']))
            $formatted['lastlogon'] = LDAPUtility::LDAPTimeToUnixTime($formatted['lastlogon']);

        return $formatted;
    }

    /**
     * @param string $cn
     * @param array $vals
     * @return bool
     * @throws EntryNotFoundException
     * @throws LDAPException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function updateUser(string $cn, array $vals): bool
    {
        foreach(array_keys($vals) as $attr)
        {
            if(!is_array($vals[$attr]) AND strlen($vals[$attr]) === 0) // Blank attributes must be empty arrays
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

            // If username has been changed, verify it is not in use elsewhere
            if($cn != $vals['userprincipalname'])
            {
                try
                {
                    // Check if username already exists
                    self::getUserDetails($vals['userprincipalname'], array('userprincipalname'));
                    throw new ValidationError(array('Login name already in use'));
                }
                catch(EntryNotFoundException $e){} // Do nothing
            }

            // Re-add domain '@'
            $vals['userprincipalname'] = $vals['userprincipalname'] . \Config::OPTIONS['ldapPrincipalSuffix'];
        }

        $c = new LDAPConnection(TRUE, TRUE);
        $user = LDAPUtility::getObject($c, $cn, ['objectguid', 'dn']);

        $hist = HistoryRecorder::writeHistory('!NETUSER', HistoryRecorder::MODIFY, $user[0]['objectguid'][0], new NetModel());

        // Format VALS for History Entry
        $histAttrs = array();

        foreach(array_keys($vals) as $attr)
        {
            if(!is_array($vals[$attr]))
                $histAttrs[$attr] = array($vals[$attr]);
            else
                $histAttrs[$attr] = $vals[$attr];
        }

        HistoryRecorder::writeAssocHistory($hist, $histAttrs);

        $res = LDAPUtility::updateEntry($c, $user[0]['dn'], $vals);

        $c->close();
        return $res;
    }

    /**
     * @param array $filterAttrs
     * @param array $getAttrs
     * @return array
     * @throws LDAPException
     */
    public static function searchUsers(array $filterAttrs, array $getAttrs = ExtConfig::OPTIONS['userReturnedSearchAttributes']): array
    {
        $c = new LDAPConnection(TRUE, TRUE);

        $results = LDAPUtility::getObjects($c, $filterAttrs, $getAttrs, array('objectClass' => 'user', 'objectCategory' => 'person'));
        $c->close();

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

            foreach($getAttrs as $attr)
            {
                if(!isset($user[$attr])) // Fill in the blanks
                    $user[$attr] = '';
            }

            $users[] = $user;
        }

        return $users;
    }

    /**
     * @param string $cn
     * @param string $imageContents
     * @return bool
     * @throws EntryNotFoundException
     * @throws LDAPException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function updateUserImage(string $cn, string $imageContents): bool
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
        $c = new LDAPConnection(TRUE, TRUE);

        $user = LDAPUtility::getObject($c, $cn, array('dn', 'objectguid'));

        $hist = HistoryRecorder::writeHistory('!NETUSER', HistoryRecorder::MODIFY, $user[0]['objectguid'][0], new NetModel());
        HistoryRecorder::writeAssocHistory($hist, array('thumbnailphoto' => ['Thumbnail Photo Replaced']));

        $res = LDAPUtility::updateEntry($c, $user[0]['dn'], array('thumbnailPhoto' => $imageContents));

        $c->close();

        return $res;
    }

    /**
     * @param string $cn
     * @param array $args // 'password' and 'confirm'
     * @return bool
     * @throws EntryNotFoundException
     * @throws LDAPException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function resetPassword(string $cn, array $args): bool
    {
        $password = (string)$args['password'];
        $confirm = (string)$args['confirm'];

        if($password != $confirm)
            throw new ValidationError(array('Passwords do not match'));

        $c = new LDAPConnection(TRUE, TRUE);

        $userGUID = LDAPUtility::getObject($c, $cn, array('objectguid'))[0]['objectguid'][0];

        $hist = HistoryRecorder::writeHistory('!NETUSER', HistoryRecorder::MODIFY, $userGUID, new NetModel());
        HistoryRecorder::writeAssocHistory($hist, array('unicodePwd' => ['User Password Reset']));

        $res = LDAPUtility::setUserPassword($c, $cn, $password);

        $c->close();
        return $res;
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
    public static function modifyGroups(string $cn, array $vals): bool
    {
        $details = self::getUserDetails($cn, array('objectguid', 'distinguishedname'));
        $userDN = $details['distinguishedname'];
        $userGUID = $details['objectguid'];

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

        $c = new LDAPConnection(TRUE, TRUE);

        $hist = HistoryRecorder::writeHistory('!NETUSER', HistoryRecorder::MODIFY, $userGUID, new NetModel());

        HistoryRecorder::writeAssocHistory($hist, array('addGroups' => $addDNs));
        HistoryRecorder::writeAssocHistory($hist, array('removeGroups' => $removeDNs));

        foreach($addDNs as $dn)
        {
            try
            {
                LDAPUtility::addAttribute($c, $dn, 'member', $userDN);
            }
            catch(LDAPException $e){} // Do nothing
        }

        foreach($removeDNs as $dn)
        {
            try
            {
                LDAPUtility::delAttribute($c, $dn, 'member', $userDN);
            }
            catch(LDAPException $e){}
        }

        $c->close();
        return TRUE;
    }

    /**
     * @param string $cn
     * @return bool
     * @throws EntryNotFoundException
     * @throws LDAPException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function deleteUser(string $cn): bool
    {
        $details = self::getUserDetails($cn, array('objectguid', 'distinguishedname'));
        $userDN = $details['distinguishedname'];
        $userGUID = $details['objectguid'];

        $c = new LDAPConnection(TRUE, TRUE);

        HistoryRecorder::writeHistory('!NETUSER', HistoryRecorder::DELETE, $userGUID, new NetModel());

        $res = LDAPUtility::deleteObject($c, $userDN);
        $c->close();
        return $res;
    }

    /**
     * @param array $attrs
     * @return array|null Array with new user login name if created, NULL if failed
     * @throws EntryNotFoundException
     * @throws LDAPException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function createUser(array $attrs): ?array
    {
        $errors = array();

        $newUsername = NULL;

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
        $newUsername = $attrs['userprincipalname']; // Store the username only for use in getting GUID
        $attrs['userprincipalname'] = $attrs['userprincipalname'] . \Config::OPTIONS['ldapPrincipalSuffix'];

        // Extract passwords
        $password = isset($attrs['password']) ? $attrs['password'] : '';
        $confirm = isset($attrs['confirm']) ? $attrs['confirm'] : '';

        // Remove password and confirm, they are not LDAP attributes
        unset($attrs['password']);
        unset($attrs['confirm']);

        // Make sure passwords match
        if($password !== $confirm)
            $errors[] = 'Passwords do not match';

        // Format password
        $attrs['unicodePwd'] = LDAPUtility::getLDAPFormattedPassword($password);

        // Throw errors, if present
        if(!empty($errors))
            throw new ValidationError($errors);

        // Add objectclass "person"
        $attrs['objectclass'] = array('top', 'person', 'organizationalPerson', 'user');

        $c = new LDAPConnection(TRUE, TRUE);

        // Extract and unset DN
        $dn = $attrs['distinguishedname'];

        // Translate useraccountcontrol
        if(isset($attrs['useraccountcontrol']) AND is_array($attrs['useraccountcontrol']))
        {
            $attrs['useraccountcontrol'] = self::flagsToUAC($attrs['useraccountcontrol']);
        }

        // Remove empty attributes
        foreach(array_keys($attrs) as $attr)
        {
            if((is_array($attrs[$attr]) AND empty($attrs[$attr])) OR (!is_array($attrs[$attr]) AND strlen($attrs[$attr]) === 0))
                unset($attrs[$attr]);
        }

        // Create object
        if(LDAPUtility::createObject($c, $dn, $attrs))
        {
            $c->close();

            // Pull CN from dn
            $cn = explode(',', $dn)[0];
            $cn = explode('CN=', $cn)[1];

            $userGUID = self::getUserDetails($cn, array('objectguid'))['objectguid'];

            $hist = HistoryRecorder::writeHistory('!NETUSER', HistoryRecorder::CREATE, $userGUID, new NetModel());

            // Format VALS for History Entry
            $histAttrs = array();

            foreach(array_keys($attrs) as $attr)
            {
                if(!is_array($attrs[$attr]))
                    $histAttrs[$attr] = array($attrs[$attr]);
                else
                    $histAttrs[$attr] = $attrs[$attr];
            }


            unset($histAttrs['unicodePwd']); // Remove unicodePwd
            HistoryRecorder::writeAssocHistory($hist, $histAttrs);

            return array('cn' => $cn);
        }

        $c->close();
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
