<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 10/30/2019
 * Time: 12:49 PM
 */


namespace extensions\itsm;


class ExtConfig
{
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
}