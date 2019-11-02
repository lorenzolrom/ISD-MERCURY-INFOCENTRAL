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
        $filter = "(|(sAMAccountName=" . $username . "))";
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
            if($filterAttrs[$attr] === NULL)
                $filterAttrs[$attr] = '';

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

        return FALSE;
    }

    /**
     * @param string $username
     * @param $newEntry
     * @return bool
     */
    public function updateLDAPEntry(string $username, $newEntry): bool
    {
        $this->bind(\Config::OPTIONS['ldapUsername'], \Config::OPTIONS['ldapPassword']);

        $attributes = array("uid");
        $user = $this->searchByUsername($username, $attributes);

        if($user['count'] != 1)
            return FALSE;

        $resultUserDN = $user[0]['dn'];

        if(ldap_mod_replace($this->connection, $resultUserDN, $newEntry))
            return TRUE;

        return FALSE;
    }
}