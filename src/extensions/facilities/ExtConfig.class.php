<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 10/30/2019
 * Time: 12:02 PM
 */


namespace extensions\facilities;


class ExtConfig
{
    // Define Ext Routes
    public const ROUTES = array(
        'buildings' => 'extensions\facilities\controllers\BuildingController',
        'locations' => 'extensions\facilities\controllers\LocationController',
        'floorplans' => 'extensions\facilities\controllers\FloorplanController'
    );
}