<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 9:08 AM
 */


namespace business;


use database\TokenDatabaseHandler;
use exceptions\EntryNotFoundException;
use exceptions\SecurityException;
use models\Token;

class TokenOperator extends Operator
{
    /**
     * @param string $token
     * @return Token
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function getToken(string $token): Token
    {
        return TokenDatabaseHandler::selectByToken($token);
    }

    /**
     * @param int $user
     * @return Token
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function generateNewToken(int $user): Token
    {
        $token = hash('SHA512', openssl_random_pseudo_bytes(2048));
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $expired = 0;
        $issueTime = date('Y-m-d H:i:s');
        $expireTime = self::getOneHourFromNow();

        return TokenDatabaseHandler::insert($token, $user, $issueTime, $expireTime, $expired, $ipAddress);
    }

    /**
     * @param Token $token
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws SecurityException
     */
    public static function validateToken(Token $token): bool
    {
        // Token marked as expired
        if($token->getExpired() === 1)
            throw new SecurityException(SecurityException::MESSAGES[SecurityException::TOKEN_EXPIRED], SecurityException::TOKEN_EXPIRED);

        // Token expiration date has passed
        if(\DateTime::createFromFormat('Y-m-d H:i:s', $token->getExpireTime()) < \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')))
        {
            try
            {
                TokenDatabaseHandler::update($token->getToken(), $token->getExpireTime(), 1);
            }
            catch(EntryNotFoundException $e){} // If token is supplied as parameter, it should exist

            throw new SecurityException(SecurityException::MESSAGES[SecurityException::TOKEN_EXPIRED], SecurityException::TOKEN_EXPIRED);
        }

        // Otherwise, update expire time
        try
        {
            TokenDatabaseHandler::update($token->getToken(), self::getOneHourFromNow(), $token->getExpired());
        }
        catch(EntryNotFoundException $e){} // if token is supplied as parameter, it should exist

        return TRUE;
    }

    /**
     * @param Token $token
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function expireToken(Token $token): bool
    {
        try
        {
            TokenDatabaseHandler::update($token->getToken(), $token->getExpireTime(), 1);
            return TRUE;
        }
        catch(EntryNotFoundException $e){}

        return FALSE;
    }

    /**
     * @return \DateTime
     */
    private static function getOneHourFromNow():string
    {
        return date('Y-m-d H:i:s', strtotime("+ 1 hours "));
    }
}