<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * MERCURY InfoCentral
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 3:32 PM
 */


namespace factories;


use database\TokenDatabaseHandler;
use models\User;
use models\Token;

class TokenFactory
{
    /**
     * @param string $token
     * @return Token
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getFromToken(string $token): Token
    {
        $tokenData = TokenDatabaseHandler::selectFromToken($token);

        return new Token($tokenData['token'],
                             $tokenData['user'],
                             $tokenData['issueTime'],
                             $tokenData['expireTime'],
                             $tokenData['expired'],
                             $tokenData['ipAddress'],);
    }

    /**
     * @param User $user
     * @return Token
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getNewToken(User $user): Token
    {
        $newToken = hash('SHA512', openssl_random_pseudo_bytes(2048));

        return self::getFromToken(TokenDatabaseHandler::insert(['token' => $newToken,
                                                                    'ipAddress' => $_SERVER['REMOTE_ADDR'],
                                                                    'user' => $user->getId()]));

    }
}