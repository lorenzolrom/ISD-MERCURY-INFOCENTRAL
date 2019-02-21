<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 4:13 PM
 */


namespace database;


use exceptions\EntryNotFoundException;
use messages\Messages;

class RoleDatabaseHandler
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

        $select = $handler->prepare("SELECT id, displayName FROM fa_Role WHERE id = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 1)
            return $select->fetch();

        throw new EntryNotFoundException(Messages::ROLE_NOT_FOUND, EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function selectAllIDs(): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT id FROM fa_Role");
        $select->execute();

        $handler->close();

        return $select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0);
    }

    /**
     * @param string $displayName
     * @return int Numerical I.D. of the newly created role
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $displayName): int
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare("INSERT INTO fa_Role(displayName) VALUES (?)");
        $insert->bindParam(1, $displayName, DatabaseConnection::PARAM_STR);
        $insert->execute();
        $newRoleID = $handler->getLastInsertId();

        $handler->close();

        return $newRoleID;
    }

    /**
     * @param int $id
     * @return array Of raw permission codes
     * @throws \exceptions\DatabaseException
     */
    public static function getRolePermissionCodes(int $id): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT permission FROM fa_Role_Permission WHERE role = ?");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0);
    }

    /**
     * @param string $displayName
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function isDisplayNameInUse(string $displayName): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT displayName FROM fa_Role WHERE BINARY displayName = ? LIMIT 1");
        $select->bindParam(1, $displayName, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param string $displayName
     * @return bool Was a record updated?
     * @throws \exceptions\DatabaseException
     */
    public static function updateDisplayName(int $id, string $displayName): bool
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare("UPDATE fa_Role set displayName = ? WHERE id = ?");
        $update->bindParam(1, $displayName, DatabaseConnection::PARAM_STR);
        $update->bindParam(2, $id, DatabaseConnection::PARAM_INT);
        $update->execute();

        $handler->close();

        return $update->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare("DELETE FROM fa_Role WHERE id = ?");
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }
}