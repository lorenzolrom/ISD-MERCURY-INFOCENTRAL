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
 * Time: 12:02 PM
 */


namespace extensions\facilities;


class ExtConfig
{
    public const EXT_VERSION = '1.0.0';

    // Define Ext Routes
    public const ROUTES = array(
        'buildings' => 'extensions\facilities\controllers\BuildingController',
        'locations' => 'extensions\facilities\controllers\LocationController',
        'floorplans' => 'extensions\facilities\controllers\FloorplanController',
        'spaces' => 'extensions\facilities\controllers\SpaceController'
    );

    public const HISTORY_OBJECTS = array(
        'building' => 'FacilitiesCore_Building',
        'location' => 'FacilitiesCore_Location',
        'floorplan' => 'Facilities_Floorplan',
    );

    public const HISTORY_PERMISSIONS = array(
        'FacilitiesCore_Building' => 'facilitiescore_facilities-r',
        'FacilitiesCore_Location' => 'facilitiescore_facilities-r',
        'Facilities_Floorplan' => 'facilitiescore_floorplans-r',
    );
}
