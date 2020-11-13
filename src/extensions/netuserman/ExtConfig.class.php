<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 11/01/2019
 * Time: 1:08 PM
 */


namespace extensions\netuserman;


class ExtConfig
{
    public const EXT_VERSION = '1.0.0';

    public const ROUTES = array(
        'netuserman' => 'extensions\netuserman\controllers\NetUserController',
        'netgroupman' => 'extensions\netuserman\controllers\NetGroupController'
    );

    public const OPTIONS = array( // Attributes allowed to be used in search filter

        'groupReturnedSearchAttributes' => array( // Attributes returned when searching for groups
            'cn',
            'description',
            'distinguishedname'
        ),

        'groupReturnedAttributes' => array( // Attributes returned when getting a single group
            'description',
            'distinguishedname',
            'member',
            'cn'
        ),

        'groupEditableAttributes' => array( // Attributes allowed to be edited in a group
            'distinguishedname',
            'description'
        ),

        'groupSearchByAttributes' => array( // Attributes allowed to be searched by in a group
            'cn',
            'description',
        ),

        'userSearchByAttributes' => array( // Attributes allowed to be searched by in a user
            'samaccountname',
            'givenname',
            'sn',
        ),

        'userReturnedSearchAttributes' => array( // Attributes returned when searching for users
            'cn',
            'userprincipalname',
            'useraccountcontrol',
            'title',
            'description'
        ),

        'userReturnedAttributes' => array( // Attributes returned when getting a single user
            'objectguid',
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
            'primarygroupid', // ID of the primary group
            'title', // Title
            'useraccountcontrol', // Disable and password expire status
            'lastlogon' // Last login of user UNIX timestamp
        ),

        'userEditableAttributes' => array( // Attributes allowed to be edited in a user
            'givenname', // First Name
            'initials', // Middle Name / Initials
            'sn', // Last Name
            'distinguishedname',
            'userprincipalname', // Login Name
            'displayname', // Display Name
            'name', // Full Name
            'description', // Description
            'physicaldeliveryofficename', // Office
            'telephonenumber', // Telephone Number
            'mail', // Email
            'title', // Title
            'useraccountcontrol' // Disable and password expire status
        )
    );

    public const HISTORY_OBJECTS = array(
        'netuser' => '!NETUSER',
        'netgroup' => '!NETGROUP',
    );

    public const HISTORY_PERMISSIONS = array(
        '!NETUSER' => 'netuserman-read',
        '!NETGROUP' => 'netuserman-readgroups',
    );

    public const HISTORY_CUSTOM_OPERATOR = array(
        '!NETUSER',
        '!NETGROUP'
    );
}
