<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 2:51 PM
 */


namespace database;


use exceptions\EntryNotFoundException;

class UserDatabaseHandler
{
    /**
     * @param int $id
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectFromID(int $id): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT id, loginName, authType, password, firstName, lastName, 
                                            displayName, email, disabled FROM fa_User WHERE id = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 1)
            return $select->fetch();

        throw new EntryNotFoundException(\messages\Messages::USER_NOT_FOUND, EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);
    }

    /**
     * @param string $loginName
     * @return array
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectFromLoginName(string $loginName): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT id FROM fa_User WHERE loginName = ? LIMIT 1");
        $select->bindParam(1, $loginName, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 1)
            return self::selectFromID($select->fetchColumn());

        throw new EntryNotFoundException(\messages\Messages::USER_NOT_FOUND, EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);
    }

    /**
     * @param int $userID
     * @throws \exceptions\DatabaseException
     */
    public static function expireAllTokensForUser(int $userID)
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare("UPDATE fa_Token SET expired = 1 WHERE user = ?");
        $update->bindParam(1, $userID, DatabaseConnection::PARAM_INT);
        $update->execute();

        $handler->close();
    }

    /**
     * @param int $userID
     * @return array List of Numerical Role IDs
     * @throws \exceptions\DatabaseException
     */
    public static function selectUserRoleIDs(int $userID): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT role FROM fa_User_Role WHERE user = ?");
        $select->bindParam(1, $userID, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0);
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectAllLoginNames(): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT loginName FROM fa_User");
        $select->execute();

        $handler->close();

        return $select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0);
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectAllIDs(): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT id FROM fa_User");
        $select->execute();

        $handler->close();

        return $select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0);
    }
}