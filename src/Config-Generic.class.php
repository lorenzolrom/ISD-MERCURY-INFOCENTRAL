<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
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

        'ldapUsername' => 'domain_admin',
        'ldapPassword' => 'domain_password',

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