<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 6:08 PM
 */


namespace factories;


use database\PermissionDatabaseHandler;
use models\Permission;

class PermissionFactory
{
    /**
     * @param string $code
     * @return Permission
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getFromCode(string $code): Permission
    {
        $permissionData = PermissionDatabaseHandler::selectFromCode($code);

        return new Permission($permissionData['code'],
                              $permissionData['displayName'],
                              $permissionData['description']);
    }
}