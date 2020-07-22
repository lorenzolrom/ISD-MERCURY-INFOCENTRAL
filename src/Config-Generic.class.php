<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 3:52 PM
 */

/**
 * Class Config
 * Configuration options
 */
abstract class Config_Generic // THIS FILE MUST BE RENAMED Config.class.php
{
    const OPTIONS = array(
        'baseURI' => '/',

        'databaseHost' => 'your.server',
        'databaseName' => 'your_database',
        'databaseUser' => 'your_user',
        'databasePassword' => 'your_password',

        'salt' => 'your_salt_here',
        'allowMultipleSessions' => FALSE,
        'allowAccessWithoutSecret' => FALSE, // Set this to true to allow access without a Secret
        // A valid session Token is required, and for user-less reads a valid Secret with the correct permissions
        // will be required
        'accessControlAllowAllOrigins' => FALSE, // This sets Access-Control-Allowed-Origins to '*'
        'authenticationMethods' => array( // Defines allowed authentication methods, valid entries are 'default' and 'v2'
            'default', // Default token-string authentication
            'v2' // JWT
        ),

        // Define extensions to be enabled
        'enabledExtensions' => array(
            'facilities',
            'itsm',
            'tickets'
        ),

        'ldapEnabled' => FALSE,
        'ldapFilter' => '', // Filter for user lookup
        'ldapVersion' => 3, // LDAP version, use '3' for Active Directory
        'ldapDomainController' => 'domain.local',
        'ldapDomain' => 'DOMAIN', // Domain prefix for user accounts
        'ldapDomainDn' => 'dc=domain, dc=local',
        'ldapPrincipalSuffix' => '@domain.local',
        'ldapTLSAttempts' => 10, // Number of times to attempt to startTLS with the domain controller

        'ldapUsername' => 'domain_admin',
        'ldapPassword' => 'domain_password',

        'ldapDefaultRoles' => array(), // Default Roles to be given to LDAP users created by the system
                                        // Must be an array of role *IDs* retrieved from database, or empty

        'emailEnabled' => FALSE,
        'emailHost' => 'ssl://email_server',
        'emailPort' => 000,
        'emailAuth' => TRUE,
        'emailUsername' => 'email_username',
        'emailPassword' => 'email_password',
        'emailFromAddress' => 'some@email.com',
        'emailFromName' => 'Some Name',

        'sshKeyPath' => '', // Path to SSH key for remote servers
    );
}
