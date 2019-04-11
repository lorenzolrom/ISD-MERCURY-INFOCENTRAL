<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 9:28 AM
 */


namespace business;


use database\SecretDatabaseHandler;
use models\Secret;

class SecretOperator extends Operator
{
    /**
     * @param string $secret
     * @return Secret
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getSecret(string $secret): Secret
    {
        return SecretDatabaseHandler::selectBySecret($secret);
    }
}