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


use database\SecretDatabaseHandler;
use models\Secret;

class SecretFactory
{
    /**
     * @param string $secret
     * @return Secret
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getFromSecret(string $secret): Secret
    {
        $appTokenData = SecretDatabaseHandler::selectFromSecret($secret);

        return new Secret($appTokenData['id'], $appTokenData['secret'], $appTokenData['name'], $appTokenData['exempt']);
    }
}