<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * MERCURY InfoCentral
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

    /**
     * @param string $loginName
     * @param string $authType
     * @param string $password
     * @param string $firstName
     * @param string $lastName
     * @param string|null $displayName
     * @param string|null $email
     * @param int $disabled
     * @return User
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getNew(string $loginName, string $authType, ?string $password, string $firstName,
                                  string $lastName, ?string $displayName, ?string $email, int $disabled)
    {
        return self::getFromID(UserDatabaseHandler::insert($loginName, $authType, $password, $firstName, $lastName,
            $displayName, $email, $disabled));
    }
}