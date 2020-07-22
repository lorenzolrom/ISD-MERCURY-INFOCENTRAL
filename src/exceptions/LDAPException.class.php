<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 1:57 PM
 */


namespace exceptions;


use Throwable;

class LDAPException extends MercuryException
{
    const FAILED_SET_LDAP_VERSION = 1200;
    const FAILED_DISABLE_REFERRALS = 1201;
    const FAILED_START_TLS = 1202;
    const OPERATION_FAILED = 1203;

    const MESSAGES = array(
        self::FAILED_SET_LDAP_VERSION => "Failed to set LDAP protocol version",
        self::FAILED_DISABLE_REFERRALS => "Failed to disable LDAP referrals",
        self::FAILED_START_TLS => "Failed to start TLS LDAP connection",
        self::OPERATION_FAILED => "The LDAP operation failed"
    );

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct('LDAP: ' . $message, $code, $previous);
    }
}
