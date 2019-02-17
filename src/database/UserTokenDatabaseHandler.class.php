<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 3:33 PM
 */


namespace database;


use exceptions\EntryNotFoundException;
use messages\Messages;

class UserTokenDatabaseHandler
{
    /**
     * @param string $token
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectFromToken(string $token): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT token, user, issueTime, expireTime, expired, ipAddress FROM fa_UserToken WHERE token = ? LIMIT 1");
        $select->bindParam(1, $token, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 1)
            return $select->fetch();

        throw new EntryNotFoundException(Messages::SECURITY_USERTOKEN_NOT_FOUND, EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);
    }

    /**
     * @param array $columns
     * @return string Supplied token (also PK of new token entry)
     * @throws \exceptions\DatabaseException
     */
    public static function insert(array $columns): string
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare("INSERT INTO fa_UserToken (token, user, issueTime, expireTime, ipAddress) 
                                            VALUES (:token, :user, NOW(), NOW() + INTERVAL 1 HOUR, :ipAddress)");
        $insert->bindParam('token', $columns['token'], DatabaseConnection::PARAM_STR);
        $insert->bindParam('user', $columns['user'], DatabaseConnection::PARAM_INT);
        $insert->bindParam('ipAddress', $columns['ipAddress'], DatabaseConnection::PARAM_STR);
        $insert->execute();

        $handler->close();

        return $columns['token'];
    }
}