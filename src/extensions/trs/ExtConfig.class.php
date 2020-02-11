<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/08/2020
 * Time: 2:32 PM
 */


namespace extensions\trs;


class ExtConfig
{
    public const EXT_VERSION = '1.0.0';

    public const ROUTES = array(
        'trs' => 'extensions\trs\controllers\TRSController',
    );

    public const OPTIONS = array();

    public const HISTORY_OBJECTS = array(
        'trsorganization' => 'TRS_Organization'
    );

    public const HISTORY_PERMISSIONS = array(
        'TRS_Organization' => 'trs_organizations-r'
    );
}