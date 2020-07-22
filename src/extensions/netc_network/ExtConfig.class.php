<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 12/17/2019
 * Time: 1:02 PM
 */


namespace extensions\netc_network;

/**
 * Extension configuration for
 *
 * NETC(NetCenter) - Networking
 *
 *  - Subnet Tracking
 *  - Computer tracking
 *  - DNS record tracking
 *  - DHCP log integration tool
 *
 * Class ExtConfig
 * @package extensions\netc_network
 */
class ExtConfig
{
    public const EXT_VERSION = '1.0.0';

    public const ROUTES = array(
        // Subnets
        'subnets' => 'extensions\netc_network\controllers\SubnetController',

        // Computers
        'computers' => 'extensions\netc_network\controllers\ComputerController',

        // DNS Records
        'dns' => 'extensions\netc_network\controllers\DNSController'
    );

    public const OPTIONS = array(
        'DHCPLogsEnabled' => FALSE, // Should DHCP logs be searchable, and referenced by Computers
    );
}
