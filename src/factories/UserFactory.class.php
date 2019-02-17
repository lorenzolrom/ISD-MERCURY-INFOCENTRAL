<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 2:55 PM
 */


namespace factories;


use database\UserDatabaseHandler;
use models\User;

class UserFactory
{
    /**
     * @param int $id
     * @return User
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getFromID(int $id): User
    {
        $userData = UserDatabaseHandler::selectFromID($id);

        return new User($userData['id'],
                        $userData['loginName'],
                        $userData['authType'],
                        $userData['password'],
                        $userData['firstName'],
                        $userData['lastName'],
                        $userData['displayName'],
                        $userData['email'],
                        $userData['disabled']);
    }

    /**
     * @param string $loginName
     * @return User
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getFromLoginName(string $loginName): User
    {
        $userData = UserDatabaseHandler::selectFromLoginName($loginName);

        return new User($userData['id'],
            $userData['loginName'],
            $userData['authType'],
            $userData['password'],
            $userData['firstName'],
            $userData['lastName'],
            $userData['displayName'],
            $userData['email'],
            $userData['disabled']);
    }
}