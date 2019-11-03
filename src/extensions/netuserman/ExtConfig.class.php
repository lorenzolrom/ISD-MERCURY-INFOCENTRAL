<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/01/2019
 * Time: 1:08 PM
 */


namespace extensions\netuserman;


class ExtConfig
{
    public const ROUTES = array(
        'netuserman' => 'extensions\netuserman\controllers\NetUserController',
        'netgroupman' => 'extensions\netuserman\controllers\NetGroupController'
    );

    public const OPTIONS = array( // Attributes allowed to be used in search filter
        'allowedSearchAttributes' => array(
            'samaccountname',
            'givenname',
            'sn'
        ),

        'returnedSearchAttributes' => array( // List of attributes returned from a bulk search
            'userprincipalname',
            'sn',
            'givenname',
            'useraccountcontrol',
            'title',
            'description'
        ),

        'allowedSearchGroupAttributes' => array(
            'cn',
            'description',
        ),

        'returnedSearchGroupAttributes' => array(
            'cn',
            'description',
            'distinguishedname'
        ),

        'usedGroupAttributes' => array(
            'description',
            'distinguishedname',
            'member',
            'cn'
        ),

        'usedAttributes' => array( // Defines default attributes returned for a user, and what attributes are allowed to be updated
            'givenname', // First Name
            'initials', // Middle Name / Initials
            'sn', // Last Name
            'cn', // Common Name
            'distinguishedname',
            'userprincipalname', // Login Name
            'displayname', // Display Name
            'name', // Full Name
            'description', // Description
            'physicaldeliveryofficename', // Office
            'telephonenumber', // Telephone Number
            'mail', // Email
            'memberof', // Add to Groups
            'title', // Title
            'useraccountcontrol' // Disable and password expire status
        )
    );
}