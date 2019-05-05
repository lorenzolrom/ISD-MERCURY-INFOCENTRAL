<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 9:04 AM
 */


namespace database;


use exceptions\EntryNotFoundException;
use models\Token;

class TokenDatabaseHandler extends DatabaseHandler
{
    /**
     * @param string $token
     * @return Token
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectByToken(string $token): Token
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `token`, `user`, `issueTime`, `expireTime`, `expired`, `ipAddress` FROM `Token` WHERE `token` = ? LIMIT 1");
        $select->bindParam(1, $token, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("models\Token");
    }

    /**
     * @param string $token
     * @param int $user
     * @param string $issueTime
     * @param string $expireTime
     * @param int $expired
     * @param string $ipAddress
     * @return Token
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $token, int $user, string $issueTime, string $expireTime, int $expired, string $ipAddress): Token
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `Token` (`token`, `user`, `issueTime`, `expireTime`, `expired`, `ipAddress`) VALUES (:token, :user, :issueTime, :expireTime, :expired, :ipAddress)');
        $insert->bindParam('token', $token, DatabaseConnection::PARAM_STR);
        $insert->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $insert->bindParam('issueTime', $issueTime, DatabaseConnection::PARAM_STR);
        $insert->bindParam('expireTime', $expireTime, DatabaseConnection::PARAM_STR);
        $insert->bindParam('expired', $expired, DatabaseConnection::PARAM_INT);
        $insert->bindParam('ipAddress', $ipAddress, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $handler->close();

        return self::selectByToken($token);
    }

    /**
     * @param string $token
     * @param string $expireTime
     * @param int $expired
     * @return Token
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(string $token, string $expireTime, int $expired): Token
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare("UPDATE `Token` SET `expireTime` = :expireTime, `expired` = :expired WHERE `token` = :token");
        $update->bindParam('expireTime', $expireTime, DatabaseConnection::PARAM_STR);
        $update->bindParam('expired', $expired, DatabaseConnection::PARAM_INT);
        $update->bindParam('token', $token, DatabaseConnection::PARAM_STR);
        $update->execute();

        $handler->close();

        return self::selectByToken($token);
    }

    /**
     * @param int $user
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function markExpiredForUser(int $user): bool
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare("UPDATE `Token` SET `expired` = 1 WHERE `user` = ?");
        $update->bindParam(1, $user, DatabaseConnection::PARAM_INT);
        $update->execute();

        $handler->close();

        return $update->getRowCount() !== 0;
    }

    /**
     * @param string|null $username
     * @param string|null $ipAddress
     * @param string|null $startTime
     * @param string|null $endTime
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function select(?string $username = '%', ?string $ipAddress = '%',
                                  ?string $startTime = '1000-01-01 00:00:00 ',
                                  ?string $endTime = '9999-12-31 23:59:59'): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `token` FROM `Token` WHERE `user` IN (SELECT `id` FROM `User` WHERE `username` 
                 LIKE :username) AND `ipAddress` LIKE :ipAddress AND `issueTime` BETWEEN :startTime AND :endTime ORDER BY `issueTime` DESC');

        $select->bindParam('username', $username, DatabaseConnection::PARAM_STR);
        $select->bindParam('ipAddress', $ipAddress, DatabaseConnection::PARAM_STR);
        $select->bindParam('startTime', $startTime, DatabaseConnection::PARAM_STR);
        $select->bindParam('endTime', $endTime, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $tokens = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $token)
        {
            try{$tokens[] = self::selectByToken($token);}
            catch(EntryNotFoundException $e){}
        }

        return $tokens;
    }
}