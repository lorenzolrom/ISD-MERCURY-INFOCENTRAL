<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 11:14 AM
 */


namespace factories;


use database\AppTokenDatabaseHandler;
use models\AppToken;

class AppTokenFactory
{
    /**
     * @param string $token
     * @return AppToken
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getFromToken(string $token): AppToken
    {
        $appTokenData = AppTokenDatabaseHandler::selectFromToken($token);

        return new AppToken($appTokenData['id'], $appTokenData['token'], $appTokenData['name']);
    }
}