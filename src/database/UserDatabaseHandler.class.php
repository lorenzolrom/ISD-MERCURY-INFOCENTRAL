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
    // Valid column names in the User table
    const COLUMNS = ['loginName', 'authType', 'password', 'firstName', 'lastName', 'displayName', 'email', 'disabled'];

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

    /**
     * @param string $loginName
     * @param string $authType
     * @param string $password
     * @param string $firstName
     * @param string $lastName
     * @param string|null $displayName
     * @param string|null $email
     * @param int $disabled
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $loginName, string $authType, ?string $password, string $firstName,
                                  string $lastName, ?string $displayName, ?string $email, int $disabled): int
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare("INSERT INTO fa_User (loginName, authType, password, firstName, lastName, 
                     displayName, email, disabled) VALUES (:loginName, :authType, :password, :firstName, :lastName, 
                     :displayName, :email, :disabled)");
        $insert->bindParam('loginName', $loginName, DatabaseConnection::PARAM_STR);
        $insert->bindParam('authType', $authType, DatabaseConnection::PARAM_STR);
        $insert->bindParam('password', $password, DatabaseConnection::PARAM_STR);
        $insert->bindParam('firstName', $firstName, DatabaseConnection::PARAM_STR);
        $insert->bindParam('lastName', $lastName, DatabaseConnection::PARAM_STR);
        $insert->bindParam('displayName', $displayName, DatabaseConnection::PARAM_STR);
        $insert->bindParam('email', $email, DatabaseConnection::PARAM_STR);
        $insert->bindParam('disabled', $disabled, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $newUserID = $handler->getLastInsertId();

        $handler->close();

        return $newUserID;
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare("DELETE FROM fa_User WHERE id = ?");
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $id ID record to update
     * @param string $column A valid column name
     * @param string|null $value New value
     * @param string $type Type of value (e.g., string, int)
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function updateStringValue(int $id, string $column, ?string $value, string $type): bool
    {
        if(!in_array($column, self::COLUMNS))
            return FALSE;

        switch($type)
        {
            case "string":
                $typeCode = DatabaseConnection::PARAM_STR;
                break;
            case "int":
                $typeCode = DatabaseConnection::PARAM_INT;
                break;
            case "null":
                $typeCode = DatabaseConnection::PARAM_NULL;
                break;
            default:
                return FALSE;
        }

        $handler = new DatabaseConnection();

        $update = $handler->prepare("UPDATE fa_User SET $column = ? WHERE id = ?");
        $update->bindParam(1, $value, $typeCode);
        $update->bindParam(2, $id, DatabaseConnection::PARAM_INT);
        $update->execute();

        $handler->close();

        return $update->getRowCount() === 1;
    }

    /**
     * @param string $loginName
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function isLoginNameTaken(string $loginName): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT loginName FROM fa_User WHERE loginName = ? LIMIT 1");
        $select->bindParam(1, $loginName, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $userID
     * @param int $roleID
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function addRoleToUser(int $userID, int $roleID): bool
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare("INSERT INTO fa_User_Role(user, role) VALUES (?, ?)");
        $insert->bindParam(1, $userID, DatabaseConnection::PARAM_INT);
        $insert->bindParam(2, $roleID, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $handler->close();

        return $insert->getRowCount() === 1;
    }

    /**
     * @param int $userID
     * @param int $roleID
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function removeRoleFromUser(int $userID, int $roleID): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare("DELETE FROM fa_User_Role WHERE user = ? AND role = ?");
        $delete->bindParam(1, $userID, DatabaseConnection::PARAM_INT);
        $delete->bindParam(2, $roleID, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }
}