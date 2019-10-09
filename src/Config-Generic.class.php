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
        'baseURL' => 'https://api.example.com',
        'baseURI' => '/',

        'databaseHost' => 'your.server',
        'databaseName' => 'your_database',
        'databaseUser' => 'your_user',
        'databasePassword' => 'your_password',

        'salt' => 'your_salt_here',
        'allowMultipleSessions' => FALSE,

        'additionalRoutes' => array(
            // Tickets
            'tickets' => 'controllers\tickets\TicketsController',

            // Devices
            'hosts' => 'controllers\itsm\HostController',
            'hostCategories' => 'controllers\itsm\HostCategoryController',

            // Inventory
            'commodities' => 'controllers\itsm\CommodityController',
            'warehouses' => 'controllers\itsm\WarehouseController',
            'vendors' => 'controllers\itsm\VendorController',
            'assets' => 'controllers\itsm\AssetController',
            'purchaseorders' => 'controllers\itsm\PurchaseOrderController',
            'discardorders' => 'controllers\itsm\DiscardOrderController',

            // Web
            'vhosts' => 'controllers\itsm\VHostController',
            'registrars' => 'controllers\itsm\RegistrarController',
            'urlaliases' => 'controllers\itsm\URLAliasController',

            // AIT
            'applications' => 'controllers\itsm\ApplicationController',

            // DHCP Logs
            'dhcplogs' => 'controllers\itsm\DHCPLogController',

            // Facilities
            'buildings' => 'controllers\facilities\BuildingController',
            'locations' => 'controllers\facilities\LocationController',
        ),

        'ldapEnabled' => FALSE,
        'ldapDomainController' => 'domain.local',
        'ldapDomain' => 'DOMAIN', // Domain prefix for user accounts
        'ldapDomainDn' => 'dc=domain, dc=local',

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

        // Specify paths allowed for web roots and logs
        'validWebRootPaths' => array(),
        'validWebLogPaths' => array(),

        // Link to be included in the ServiceCenter emails
        'serviceCenterAgentURL' => '', // For agent emails
        'serviceCenterRequestURL' => '', // For customer emails

        'sshKeyPath' => '', // Path to SSH key for remote servers

        'dhcpServer' => '', // IP to get DHCP logs from
        'dhcpUser' => '', // Username to remote into dhcp server
        'dhcpLogPath' => '' // Log to path on remote dhcp server
    );
}