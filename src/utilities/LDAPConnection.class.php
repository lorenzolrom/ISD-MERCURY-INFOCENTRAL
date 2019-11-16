<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/15/2019
 * Time: 8:42 PM
 */


namespace utilities;


use exceptions\LDAPException;

/**
 * Class LDAPConnectionX
 *
 * Re-factored LDAP connection only containing the connection itself
 *
 * @package utilities
 */
class LDAPConnection
{
    private $connection;
    private $domainController;
    private $bound;
    private $domain;

    /**
     * LDAPConnectionX constructor.
     * @param bool $useTLS
     * @param bool $autoBind
     * @throws LDAPException
     */
    public function __construct(bool $useTLS = TRUE, bool $autoBind = FALSE)
    {
        $this->domainController = \Config::OPTIONS['ldapDomainController'];
        $this->domain = \Config::OPTIONS['ldapDomain'];

        $this->connection = ldap_connect($this->domainController);

        $this->bound = FALSE;

        if($useTLS)
            $this->startTLS();

        if($autoBind)
            $this->bind();
    }

    /**
     * Get the connection resource
     * @return resource
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Is this connection bound?
     * @return bool
     */
    public function isBound(): bool
    {
        return $this->bound;
    }

    /**
     * Initiates a secure LDAP Connection
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
     * Bind to the directory with supplied Username and Password
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
     * Closes the LDAP Connection
     */
    public function close()
    {
        ldap_close($this->connection);
    }
}