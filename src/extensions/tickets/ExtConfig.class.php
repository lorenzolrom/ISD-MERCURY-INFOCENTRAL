<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 10/30/2019
 * Time: 1:03 PM
 */


namespace extensions\tickets;


class ExtConfig
{
    public const ROUTES = array(
        'tickets' => 'extensions\tickets\controllers\TicketsController',
    );

    public const OPTIONS = array(
        // Link to be included in the ServiceCenter emails
        'serviceCenterAgentURL' => '', // For agent emails
        'serviceCenterRequestURL' => '', // For customer emails
    );
}