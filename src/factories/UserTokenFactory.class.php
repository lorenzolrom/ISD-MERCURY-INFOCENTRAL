<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 3:32 PM
 */


namespace factories;


use database\UserTokenDatabaseHandler;
use models\User;
use models\UserToken;

class UserTokenFactory
{
    /**
     * @param string $token
     * @return UserToken
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getFromToken(string $token): UserToken
    {
        $tokenData = UserTokenDatabaseHandler::selectFromToken($token);

        return new UserToken($tokenData['token'],
                             $tokenData['user'],
                             $tokenData['issueTime'],
                             $tokenData['expireTime'],
                             $tokenData['expired'],
                             $tokenData['ipAddress'],);
    }

    /**
     * @param User $user
     * @return UserToken
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getNewToken(User $user): UserToken
    {
        $newToken = hash('SHA512', openssl_random_pseudo_bytes(2048));

        return self::getFromToken(UserTokenDatabaseHandler::insert(['token' => $newToken,
                                                                    'ipAddress' => $_SERVER['REMOTE_ADDR'],
                                                                    'user' => $user->getId()]));

    }
}