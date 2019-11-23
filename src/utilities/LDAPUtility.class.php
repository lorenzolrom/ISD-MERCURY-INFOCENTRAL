<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/15/2019
 * Time: 8:46 PM
 */


namespace utilities;


use exceptions\LDAPException;

/**
 * Class LDAPUtility
 *
 * Tools for performing operations on LDAP objects
 *
 * @package utilities
 */
class LDAPUtility
{
    //////////
    /// FORMAT CONVERTERS
    //////////
    ///
    /**
     * Formats a string password properly for LDAP
     * @param string $password
     * @return string
     */
    public static function getLDAPFormattedPassword(string $password): string
    {

        return mb_convert_encoding(("\"" . $password . "\""), 'UTF-16LE');
    }

    /**
     * Convert Microsoft Epoch to UNIX Epoch
     *
     * @param $time
     * @return string
     */
    public static function LDAPTimeToUnixTime($time): string
    {
        $secsAfterADEpoch = $time / 10000000;
        $ADToUnixConverter = ((1970 - 1601) * 365 - 3 + round((1970 - 1601) / 4)) * 86400;

        return intval($secsAfterADEpoch - $ADToUnixConverter);
    }

    /**
     * Convert UNIX Epoch to Microsoft Epoch
     *
     * @param $time
     * @return string
     */
    public static function UnixTimeToLDAPTime($time): string
    {
        $ADToUnixConverter = ((1970 - 1601) * 365 - 3 + round((1970 - 1601) / 4)) * 86400;
        $secsAfterADEpoch = intval($ADToUnixConverter + $time);
        return $secsAfterADEpoch * 10000000;
    }

    //////////
    /// OBJECT SELECTION
    //////////

    /**
     * Get a user object by userprincipalname
     *
     * @param LDAPConnection $c
     * @param string $username
     * @param array $attributes
     * @return array
     */
    public static function getUserByUsername(LDAPConnection $c, string $username, array $attributes): array
    {
        $filter = "(|(userprincipalname=" . $username . \Config::OPTIONS['ldapPrincipalSuffix'] . "))";
        $search = ldap_search($c->getConnection(), \Config::OPTIONS['ldapDomainDn'], $filter, $attributes);

        $results = ldap_get_entries($c->getConnection(), $search);

        // Convert ObjectGUID to hex if present
        for($i = 0; $i < (int)$results['count']; $i++)
        {
            if(isset($results[$i]['objectguid']))
            {
                $results[$i]['objectguid'][0] = bin2hex($results[$i]['objectguid'][0]);
            }
        }

        return $results;
    }

    /**
     * Search for a username matching the specified filter
     *
     * @param LDAPConnection $c
     * @param string $username
     * @param string $filter
     * @param array $getAttrs
     * @return array
     */
    public static function getUserMatchingFilter(LDAPConnection $c, string $username, string $filter, array $getAttrs): array
    {
        if(strlen($username) < 1)
            return array('count' => 0);

        $filter = str_replace('${user}', $username . \Config::OPTIONS['ldapPrincipalSuffix'], $filter);

        $search = ldap_search($c->getConnection(), \Config::OPTIONS['ldapDomainDn'], $filter, $getAttrs);
        return ldap_get_entries($c->getConnection(), $search);
    }

    /**
     * Get an object by Common Name
     *
     * @param LDAPConnection $c
     * @param string $cn
     * @param $attributes
     * @return array
     */
    public static function getObject(LDAPConnection $c, string $cn, $attributes): array
    {
        $filter = "(|(cn=" . $cn . "))";
        $search = ldap_search($c->getConnection(), \Config::OPTIONS['ldapDomainDn'], $filter, $attributes);

        $results = ldap_get_entries($c->getConnection(), $search);

        // Convert ObjectGUID to hex if present
        for($i = 0; $i < (int)$results['count']; $i++)
        {
            if(isset($results[$i]['objectguid']))
            {
                $results[$i]['objectguid'][0] = bin2hex($results[$i]['objectguid'][0]);
            }
        }

        return $results;
    }

    /**
     * Searches specified attributes and returns users
     *
     * To get users: punchThroughAttrs[objectClass => user, objectCategory => person]
     * To get groups: punchThroughAttrs[objectClass => group]
     *
     * @param LDAPConnection $c
     * @param array $filterAttrs
     * @param array $returnAttrs
     * @param array $punchThroughAttrs // Attributes that will not have a wildcard applied
     * @return array
     */
    public static function getObjects(LDAPConnection $c, array $filterAttrs, array $returnAttrs, array $punchThroughAttrs = []): array
    {
        $filter = '(&'; // Search for all filters (e.g. Filter AND Filter AND...)

        // Build filter
        foreach(array_keys($filterAttrs) as $attr)
        {
            if($filterAttrs[$attr] === NULL OR strlen($filterAttrs[$attr]) === 0)
                continue;

            $filter .= "($attr=*{$filterAttrs[$attr]}*)";
        }

        foreach(array_keys($punchThroughAttrs) as $attr)
        {
            if($punchThroughAttrs[$attr] === NULL OR strlen($punchThroughAttrs[$attr]) === 0)
                continue;

            $filter .= "($attr={$punchThroughAttrs[$attr]})";
        }

        $filter .= ')';

        $filter = str_replace('**', '*', $filter); // Double ** is a bad filter

        $search = ldap_search($c->getConnection(), \Config::OPTIONS['ldapDomainDn'], $filter, $returnAttrs);

        $results = ldap_get_entries($c->getConnection(), $search);

        if(!is_array($results))
            return array();

        // Convert ObjectGUID to hex if present
        for($i = 0; $i < (int)$results['count']; $i++)
        {
            if(isset($results[$i]['objectguid']))
            {
                $results[$i]['objectguid'][0] = bin2hex($results[$i]['objectguid'][0]);
            }
        }

        return $results;
    }

    //////////
    /// OBJECT CREATION
    //////////

    /**
     * @param LDAPConnection $c
     * @param string $dn
     * @param array $attrs
     * @return bool
     * @throws LDAPException
     */
    public static function createObject(LDAPConnection $c, string $dn, array $attrs): bool
    {
        if(!ldap_add($c->getConnection(), $dn, $attrs))
            throw new LDAPException(ldap_error($c->getConnection()), LDAPException::OPERATION_FAILED);

        return TRUE;
    }

    //////////
    /// OBJECT MODIFICATION
    //////////

    /**
     * Sets the password of the provided username
     *
     * @param LDAPConnection $c
     * @param $cn
     * @param $password
     * @return bool
     * @throws LDAPException
     */
    public static function setUserPassword(LDAPConnection $c, $cn, $password): bool
    {
        $c->bind(\Config::OPTIONS['ldapUsername'], \Config::OPTIONS['ldapPassword']);

        $user = self::getObject($c, $cn, array('dn'));

        if($user['count'] != 1)
            return FALSE;

        $dn = $user[0]['dn'];

        $newEntry = array('unicodePwd' => self::getLDAPFormattedPassword($password));

        if(ldap_mod_replace($c->getConnection(), $dn, $newEntry))
            return TRUE;

        throw new LDAPException(ldap_error($c->getConnection()), LDAPException::OPERATION_FAILED);
    }

    /**
     * Update LDAP entry by DN
     *
     * @param LDAPConnection $c
     * @param string $dn
     * @param array $newEntry
     * @return bool
     * @throws LDAPException
     */
    public static function updateEntry(LDAPConnection $c, string $dn, array $newEntry): bool
    {
        if(isset($newEntry['distinguishedname']))
        {
            $newDNParts = explode(',', $newEntry['distinguishedname']);
            $newCN = array_shift($newDNParts);
            unset($newEntry['distinguishedname']);

            // Build new DN
            $newDN = implode(',', $newDNParts);

            if(ldap_rename($c->getConnection(), $dn, $newCN, $newDN, TRUE))
                $dn = $newCN . ',' . $newDN;
        }

        if(ldap_mod_replace($c->getConnection(), $dn, $newEntry))
            return TRUE;

        throw new LDAPException(ldap_error($c->getConnection()), LDAPException::OPERATION_FAILED);
    }

    /**
     * @param LDAPConnection $c
     * @param string $dn
     * @param string $attrName
     * @param string $newVal
     * @return bool
     * @throws LDAPException
     */
    public static function addAttribute(LDAPConnection $c, string $dn, string $attrName, string $newVal): bool
    {
        if(!ldap_mod_add($c->getConnection(), $dn, array($attrName => $newVal)))
            throw new LDAPException(ldap_error($c->getConnection()), LDAPException::OPERATION_FAILED);

        return TRUE;
    }

    /**
     * @param LDAPConnection $c
     * @param string $dn
     * @param string $attrName
     * @param string $oldVal
     * @return bool
     * @throws LDAPException
     */
    public static function delAttribute(LDAPConnection $c, string $dn, string $attrName, string $oldVal): bool
    {
        if(!ldap_mod_del($c->getConnection(), $dn, array($attrName => $oldVal)))
            throw new LDAPException(ldap_error($c->getConnection()), LDAPException::OPERATION_FAILED);

        return TRUE;
    }

    //////////
    /// OBJECT DELETION
    //////////

    /**
     * @param LDAPConnection $c
     * @param string $dn
     * @return bool
     * @throws LDAPException
     */
    public static function deleteObject(LDAPConnection $c, string $dn): bool
    {
        if(!ldap_delete($c->getConnection(), $dn))
            throw new LDAPException(ldap_error($c->getConnection()), LDAPException::OPERATION_FAILED);

        return TRUE;
    }
}