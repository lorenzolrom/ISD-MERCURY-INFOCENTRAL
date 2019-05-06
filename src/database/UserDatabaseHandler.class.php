<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/06/2019
 * Time: 3:39 PM
 */


namespace database;


use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use models\User;

class UserDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return User
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): User
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id`, `username`, `firstName`, `lastName`, `email`, `password`, `disabled`, `authType` FROM `User` WHERE `id` = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("models\User");
    }

    /**
     * @param string $username
     * @return User
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectByUsername(string $username): User
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `User` WHERE `username` = ? LIMIT 1");
        $select->bindParam(1, $username, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::UNIQUE_KEY_NOT_FOUND], EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        return self::selectById($select->fetchColumn());
    }

    /**
     * @param string $username
     * @param string $firstName
     * @param string $lastName
     * @param array $disabled
     * @return User[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $username = "%", string $firstName = "%", string $lastName = "%", $disabled = array()): array
    {
        $query = "SELECT `id` FROM `User` WHERE `username` LIKE :username AND `firstName` LIKE :firstName AND `lastName` LIKE :lastName";

        if(is_array($disabled) AND !empty($disabled))
            $query .= " AND disabled IN (" . self::getBooleanString($disabled) . ")";

        $handler = new DatabaseConnection();

        $select = $handler->prepare($query);
        $select->bindParam('username', $username, DatabaseConnection::PARAM_STR);
        $select->bindParam('firstName', $firstName, DatabaseConnection::PARAM_STR);
        $select->bindParam('lastName', $lastName, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $users = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $users[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $users;
    }

    /**
     * @param int $id
     * @param string|null $password
     * @return User
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function updatePassword(int $id, ?string $password): User
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare("UPDATE `User` SET `password` = :password WHERE id = :id");
        $update->bindParam('password', $password, DatabaseConnection::PARAM_STR);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectUsernameFromId(int $id): ?string
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `username` FROM `User` WHERE id = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 0)
            return NULL;

        return $select->fetchColumn();
    }

    /**
     * @param string $username
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectIdFromUsername(string $username): ?int
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT `id` FROM `User` WHERE `username` = ? LIMIT 1");
        $select->bindParam(1, $username, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        if($select->getRowCount() === 0)
            return NULL;

        return $select->fetchColumn();
    }

    /**
     * @param string $username
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string|null $password
     * @param int $disabled
     * @param string $authType
     * @return User
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $username, string $firstName, string $lastName, string $email,
                                  ?string $password, int $disabled, string $authType): User
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `User` (`username`, `firstName`, `lastName`, `email`, `password`, 
                    `disabled`, `authType`) VALUES (:username, :firstName, :lastName, :email, :password, :disabled, 
                                                    :authType)');
        $insert->bindParam('username', $username, DatabaseConnection::PARAM_STR);
        $insert->bindParam('firstName', $firstName, DatabaseConnection::PARAM_STR);
        $insert->bindParam('lastName', $lastName, DatabaseConnection::PARAM_STR);
        $insert->bindParam('email', $email, DatabaseConnection::PARAM_STR);
        $insert->bindParam('password', $password, DatabaseConnection::PARAM_STR);
        $insert->bindParam('disabled', $disabled, DatabaseConnection::PARAM_INT);
        $insert->bindParam('authType', $authType, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $username
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param int $disabled
     * @param string $authType
     * @return User
     * @throws DatabaseException
     * @throws EntryNotFoundException
     */
    public static function update(int $id, string $username, string $firstName, string $lastName, string $email,
                                  int $disabled, string $authType): User
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `User` SET `username` = :username, `firstName` = :firstName, 
                  `lastName` = :lastName, `email` = :email, `disabled` = :disabled, 
                  `authType` = :authType WHERE `id` = :id');
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->bindParam('username', $username, DatabaseConnection::PARAM_STR);
        $update->bindParam('firstName', $firstName, DatabaseConnection::PARAM_STR);
        $update->bindParam('lastName', $lastName, DatabaseConnection::PARAM_STR);
        $update->bindParam('email', $email, DatabaseConnection::PARAM_STR);
        $update->bindParam('disabled', $disabled, DatabaseConnection::PARAM_INT);
        $update->bindParam('authType', $authType, DatabaseConnection::PARAM_STR);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `User` WHERE `id` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param array $roles
     * @return int
     * @throws DatabaseException
     */
    public static function setRoles(int $id, array $roles): int
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `User_Role` WHERE `user` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $insert = $handler->prepare('INSERT INTO `User_Role` (`user`, `role`) VALUES (:user, :role)');
        $insert->bindParam('user', $id, DatabaseConnection::PARAM_INT);
        $count = 0;

        foreach($roles as $role)
        {
            try
            {
                $insert->bindParam('role', $role, DatabaseConnection::PARAM_INT);
                $insert->execute();
                $count++;
            }
            catch(DatabaseException $e){}
        }

        $handler->close();

        return $count;
    }

    /**
     * @param int $role
     * @return User[]
     * @throws DatabaseException
     */
    public static function selectByRole(int $role): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `user` FROM `User_Role` WHERE `role` = ?');
        $select->bindParam(1, $role, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $users = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $users[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $users;
    }
}