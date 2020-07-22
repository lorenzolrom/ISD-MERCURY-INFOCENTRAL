<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/02/2020
 * Time: 7:27 PM
 */


namespace extensions\cliff;


class ExtConfig
{
    public const EXT_VERSION = '1.0.0';

    public const ROUTES = array(
        'locksystems' => 'extensions\cliff\controllers\SystemController',
        'lockkeys' => 'extensions\cliff\controllers\KeyController',
        'lockcores' => 'extensions\cliff\controllers\CoreController',
        'lockprocess' => 'extensions\cliff\controllers\ProcessController',
        'lockadvanced' => 'extensions\cliff\controllers\AdvancedController'
    );

    public const HISTORY_OBJECTS = array(
        'locksystem' => 'CLIFF_System',
        'lockkey' => 'CLIFF_Key',
        'lockcore' => 'CLIFF_Core',
        'lockkeyissue' => 'CLIFF_KeyIssue',
        'lockcorelocation' => 'CLIFF_CoreLocation'
    );

    public const HISTORY_PERMISSIONS = array(
        'CLIFF_System' => 'cliff-r',
        'CLIFF_Key' => 'cliff-r',
        'CLIFF_Core' => 'cliff-r',
        'CLIFF_KeyIssue' => 'cliff-r',
        'CLIFF_CoreLocation' => 'cliff-r'
    );
}
