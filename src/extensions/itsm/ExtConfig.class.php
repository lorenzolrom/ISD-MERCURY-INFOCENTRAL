<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 10/30/2019
 * Time: 12:49 PM
 */


namespace extensions\itsm;


class ExtConfig
{
    public const EXT_VERSION = '1.0.0';

    public const ROUTES = array(
        // Devices
        'hosts' => 'extensions\itsm\controllers\HostController',
        'hostCategories' => 'extensions\itsm\controllers\HostCategoryController',

        // Inventory
        'commodities' => 'extensions\itsm\controllers\CommodityController',
        'warehouses' => 'extensions\itsm\controllers\WarehouseController',
        'vendors' => 'extensions\itsm\controllers\VendorController',
        'assets' => 'extensions\itsm\controllers\AssetController',
        'purchaseorders' => 'extensions\itsm\controllers\PurchaseOrderController',
        'discardorders' => 'extensions\itsm\controllers\DiscardOrderController',

        // Web
        'vhosts' => 'extensions\itsm\controllers\VHostController',
        'registrars' => 'extensions\itsm\controllers\RegistrarController',
        'urlaliases' => 'extensions\itsm\controllers\URLAliasController',

        // AIT
        'applications' => 'extensions\itsm\controllers\ApplicationController',

        // DHCP Logs
        'dhcplogs' => 'extensions\itsm\controllers\DHCPLogController',
    );

    public const OPTIONS = array(
        // Specify paths allowed for web roots and logs
        'validWebRootPaths' => array(),
        'validWebLogPaths' => array(),

        'dhcpServer' => '', // IP to get DHCP logs from
        'dhcpUser' => '', // Username to remote into dhcp server
        'dhcpLogPath' => '' // Log to path on remote dhcp server
    );

    public const HISTORY_OBJECTS = array(
        'hostcategory' => 'ITSM_HostCategory',
        'asset' => 'ITSM_Asset',
        'commodity' => 'ITSM_Commodity',
        'host' => 'ITSM_Host',
        'vendor' => 'ITSM_Vendor',
        'warehouse' => 'ITSM_Warehouse',
        'registrar' => 'ITSM_Registrar',
        'vhost' => 'ITSM_VHost',
        'urlalias' => 'NIS_URLAlias',
        'application' => 'ITSM_Application',
        'purchaseorder' => 'ITSM_PurchaseOrder',
        'discardorder' => 'ITSM_DiscardOrder',
    );

    public const HISTORY_PERMISSIONS = array(
        'ITSM_Asset' => 'itsm_inventory-assets-r',
        'ITSM_Commodity' => 'itsm_inventory-commodities-r',
        'ITSM_Host' => 'itsm_devices-hosts-r',
        'ITSM_Vendor' => 'itsm_inventory-vendors-r',
        'ITSM_Warehouse' => 'itsm_inventory-warehouses-r',
        'ITSM_Registrar' => 'itsm_web-registrars-r',
        'ITSM_VHost' => 'itsm_web-vhosts-r',
        'NIS_URLAlias' => 'itsm_web-aliases-rw',
        'ITSM_Application' => 'itsm_ait-apps-r',
        'ITSM_HostCategory' => 'itsmmonitor-hosts-w',
        'ITSM_PurchaseOrder' => 'itsm_inventory-purchaseorders-r',
        'ITSM_DiscardOrder' => 'itsm_inventory-discards-r',
    );
}
