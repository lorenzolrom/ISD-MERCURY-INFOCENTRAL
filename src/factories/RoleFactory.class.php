<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * MERCURY InfoCentral
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 5:15 PM
 */


namespace factories;


use database\RoleDatabaseHandler;
use models\Role;

class RoleFactory
{
    /**
     * @param int $id
     * @return Role
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getFromID(int $id): Role
    {
        $roleData = RoleDatabaseHandler::selectFromID($id);

        return new Role($roleData['id'],
            $roleData['displayName']);
    }

    /**
     * @param string $displayName
     * @return Role
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getNew(string $displayName): Role
    {
        return RoleFactory::getFromID(RoleDatabaseHandler::insert($displayName));
    }
}