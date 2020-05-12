<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
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
        'lockprocess' => 'extensions\cliff\controllers\ProcessController'
    );

    public const HISTORY_OBJECTS = array(
        'locksystems' => 'CLIFF_System',
        'lockkeys' => 'CLIFF_Key',
        'lockcores' => 'CLIFF_Core'
    );

    public const HISTORY_PERMISSIONS = array(
        'CLIFF_System' => 'cliff-r',
        'CLIFF_Key' => 'cliff-r',
        'CLIFF_Core' => 'cliff-r'
    );
}