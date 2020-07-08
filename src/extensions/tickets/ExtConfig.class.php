<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 10/30/2019
 * Time: 1:03 PM
 */


namespace extensions\tickets;


class ExtConfig
{
    public const EXT_VERSION = '1.0.0';

    public const ROUTES = array(
        'tickets' => 'extensions\tickets\controllers\TicketsController',
    );

    public const OPTIONS = array(
        // Link to be included in the ServiceCenter emails
        'serviceCenterAgentURL' => '', // For agent emails
        'serviceCenterRequestURL' => '', // For customer emails

        'lockTimeoutSeconds' => 5 // Number of seconds of inactivity for a TicketLock to be inactive
    );

    public const HISTORY_OBJECTS = array(
        'workspace' => 'Tickets_Workspace',
        'team' => 'Tickets_Team',
        'ticket' => 'Tickets_Ticket',
    );

    public const HISTORY_PERMISSIONS = array(
        'Tickets_Workspace' => 'tickets-admin',
        'Tickets_Team' => 'tickets-admin',
        'Tickets_Ticket' => 'tickets-agent',
    );
}
