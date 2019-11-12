<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 1:51 PM
 */


namespace utilities;


use exceptions\LDAPException;

class LDAPConnection
{
    private $connection;
    private $domainController;
    private $bound;
    private $domain;
    private $domainDN;

    /**
     * @param string $password
     * @return string
     */
    public static function getLDAPFormattedPassword(string $password): string
    {

        return mb_convert_encoding(("\"" . $password . "\""), 'UTF-16LE');
    }

    /**
     * LDAPConnection constructor.
     * @param bool $useTLS
     * @throws LDAPException
     */
    public function __construct(bool $useTLS = TRUE)
    {
        $this->domainController = \Config::OPTIONS['ldapDomainController'];
        $this->domain = \Config::OPTIONS['ldapDomain'];
        $this->domainDN = \Config::OPTIONS['ldapDomainDn'];

        $this->connection = ldap_connect($this->domainController);

        $this->bound = FALSE;

        if($useTLS)
            $this->startTLS();
    }

    /**
     * Start a secure LDAP connection
     *
     * @throws LDAPException
     */
    public function startTLS()
    {
        if(!ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3))
            throw new LDAPException(LDAPException::MESSAGES[LDAPException::FAILED_SET_LDAP_VERSION], LDAPException::FAILED_SET_LDAP_VERSION);

        if(!ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0))
            throw new LDAPException(LDAPException::MESSAGES[LDAPException::FAILED_DISABLE_REFERRALS], LDAPException::FAILED_DISABLE_REFERRALS);

        if(!ldap_start_tls($this->connection))
            throw new LDAPException(LDAPException::MESSAGES[LDAPException::FAILED_START_TLS], LDAPException::FAILED_START_TLS);
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function bind(string $username = \Config::OPTIONS['ldapUsername'], string $password = \Config::OPTIONS['ldapPassword']): bool
    {
        set_error_handler(function(){}); // Prevents incorrect LDAP login from throwing a warning

        if(ldap_bind($this->connection, $this->domain . "\\" . $username, $password))
            $this->bound = TRUE;

        restore_error_handler();
        return $this->bound;
    }

    /**
     * Searches for an lDAP user and returns the requested attributes
     *
     * @param $username
     * @param $attributes
     * @return array
     */
    public function searchByUsername($username, array $attributes): array
    {
        $filter = "(|(userprincipalname=" . $username . \Config::OPTIONS['ldapPrincipalSuffix'] . "))";
        $search = ldap_search($this->connection, $this->domainDN, $filter, $attributes);
        return ldap_get_entries($this->connection, $search);
    }

    /**
     * @param array $filterAttrs Attributes to search by
     * @param array $attributes Attributes to obtain
     * @return array
     */
    public function searchUsers(array $filterAttrs, array $attributes): array
    {
        $filter = '(&'; // Search for all filters (e.g. Filter AND Filter AND...)

        // Build filter
        foreach(array_keys($filterAttrs) as $attr)
        {
            if($filterAttrs[$attr] === NULL OR strlen($filterAttrs[$attr]) === 0)
                continue;

            $filter .= "($attr=*{$filterAttrs[$attr]}*)";
        }

        $filter .= '(objectClass=user)(objectCategory=person))'; // Limit to user accounts

        $filter = str_replace('**', '*', $filter); // Double ** is a bad filter

        $search = ldap_search($this->connection, $this->domainDN, $filter, $attributes);

        $results = ldap_get_entries($this->connection, $search);

        return is_array($results) ? $results : array();
    }

    /**
     * Attempts to update an LDAP user's password
     *
     * @param $username
     * @param $password
     * @return bool Was the password update successful
     * @throws LDAPException
     */
    public function setPassword($username, $password): bool
    {
        $this->bind(\Config::OPTIONS['ldapUsername'], \Config::OPTIONS['ldapPassword']);

        $attributes = array("uid");
        $user = $this->searchByUsername($username, $attributes);

        if($user['count'] != 1)
            return FALSE;

        $resultUserDN = $user[0]['dn'];

        $newLDAPEntry = array('unicodePwd' => mb_convert_encoding(("\"" . $password . "\""), 'UTF-16LE'));

        if(ldap_mod_replace($this->connection, $resultUserDN, $newLDAPEntry))
            return TRUE;

        throw new LDAPException(ldap_error($this->connection), LDAPException::OPERATION_FAILED);
    }

    /**
     * @param string $username
     * @param $newEntry
     * @return bool
     * @throws LDAPException
     */
    public function updateUser(string $username, $newEntry): bool
    {
        $this->bind(\Config::OPTIONS['ldapUsername'], \Config::OPTIONS['ldapPassword']);

        $attributes = array("uid");
        $user = $this->searchByUsername($username, $attributes);

        if($user['count'] != 1)
            return FALSE;

        $resultUserDN = $user[0]['dn'];

        // Check for DN changes
        if(isset($newEntry['distinguishedname']))
        {
            $newDNParts = explode(',', $newEntry['distinguishedname']);
            $newCN = array_shift($newDNParts);
            unset($newEntry['distinguishedname']);

            // Build new DN
            $newDN = implode(',', $newDNParts);

            if(ldap_rename($this->connection, $resultUserDN, $newCN, $newDN, TRUE))
                $resultUserDN = $newCN . ',' . $newDN;
        }

        if(ldap_mod_replace($this->connection, $resultUserDN, $newEntry))
            return TRUE;

        throw new LDAPException(ldap_error($this->connection), LDAPException::OPERATION_FAILED);
    }

    /**
     * @param string $dn
     * @param array $newEntry
     * @return bool
     * @throws LDAPException
     */
    public function updateEntry(string $dn, array $newEntry): bool
    {
        if(isset($newEntry['distinguishedname']))
        {
            $newDNParts = explode(',', $newEntry['distinguishedname']);
            $newCN = array_shift($newDNParts);
            unset($newEntry['distinguishedname']);

            // Build new DN
            $newDN = implode(',', $newDNParts);

            if(ldap_rename($this->connection, $dn, $newCN, $newDN, TRUE))
                $dn = $newCN . ',' . $newDN;
        }

        if(ldap_mod_replace($this->connection, $dn, $newEntry))
            return TRUE;

        throw new LDAPException(ldap_error($this->connection), LDAPException::OPERATION_FAILED);
    }

    /**
     * Search for groups
     * @param array $filterAttrs
     * @param array $attributes
     * @return array
     */
    public function searchGroups(array $filterAttrs, array $attributes): array
    {
        $filter = '(&'; // Search for all filters (e.g. Filter AND Filter AND...)

        // Build filter
        foreach(array_keys($filterAttrs) as $attr)
        {
            if($filterAttrs[$attr] === NULL OR strlen($filterAttrs[$attr]) === 0)
                continue;

            $filter .= "($attr=*{$filterAttrs[$attr]}*)";
        }

        $filter = str_replace('**', '*', $filter); // Double ** is a bad filter
        $filter .= '(objectClass=group))'; // Limit to user accounts

        $search = ldap_search($this->connection, $this->domainDN, $filter, $attributes);

        $results = ldap_get_entries($this->connection, $search);

        return is_array($results) ? $results : array();
    }

    /**
     * @param string $cn
     * @param $attributes
     * @return array
     */
    public function getGroup(string $cn, $attributes): array
    {
        $filter = "(|(cn=" . $cn . "))";
        $search = ldap_search($this->connection, $this->domainDN, $filter, $attributes);
        return ldap_get_entries($this->connection, $search);
    }

    /**
     * @param $dn
     * @param $newCN
     * @param $newOU
     * @return bool
     * @throws LDAPException
     */
    public function rename($dn, $newCN, $newOU): bool
    {
        if(!ldap_rename($this->connection, $dn, $newCN, $newOU, true))
            throw new LDAPException(ldap_error($this->connection), LDAPException::OPERATION_FAILED);

        return TRUE;
    }

    /**
     * @param $dn
     * @param $attrName
     * @param $newVal
     * @return bool
     * @throws LDAPException
     */
    public function addAttribute($dn, $attrName, $newVal): bool
    {
        if(!ldap_mod_add($this->connection, $dn, array($attrName => $newVal)))
            throw new LDAPException(ldap_error($this->connection), LDAPException::OPERATION_FAILED);

        return TRUE;
    }

    /**
     * @param $dn
     * @param $attrName
     * @param $oldVal
     * @return bool
     * @throws LDAPException
     */
    public function delAttribute($dn, $attrName, $oldVal): bool
    {
        if(!ldap_mod_del($this->connection, $dn, array($attrName => $oldVal)))
            throw new LDAPException(ldap_error($this->connection), LDAPException::OPERATION_FAILED);

        return TRUE;
    }

    /**
     * @param $dn
     * @return bool
     * @throws LDAPException
     */
    public function deleteObject(string $dn): bool
    {
        if(!ldap_delete($this->connection, $dn))
            throw new LDAPException(ldap_error($this->connection), LDAPException::OPERATION_FAILED);

        return TRUE;
    }

    /**
     * @param string $dn
     * @param array $attrs
     * @return bool
     * @throws LDAPException
     */
    public function createObject(string $dn, array $attrs): bool
    {
        if(!ldap_add($this->connection, $dn, $attrs))
            throw new LDAPException(ldap_error($this->connection), LDAPException::OPERATION_FAILED);

        return TRUE;
    }

    /**
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
     * @param $time
     * @return string
     */
    public static function UnixTimeToLDAPTime($time): string
    {
        $ADToUnixConverter = ((1970 - 1601) * 365 - 3 + round((1970 - 1601) / 4)) * 86400;
        $secsAfterADEpoch = intval($ADToUnixConverter + $time);
        return $secsAfterADEpoch * 10000000;
    }
}