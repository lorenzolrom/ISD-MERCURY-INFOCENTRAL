<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/13/2019
 * Time: 5:59 PM
 */


namespace extensions\tickets\database;


use business\UserOperator;
use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use extensions\tickets\models\Team;
use models\User;

class TeamDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Team
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Team
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id`, `name` FROM `Tickets_Team` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('extensions\tickets\models\Team');
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function select(): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Tickets_Team`');
        $select->execute();

        $handler->close();

        $workspaces = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$workspaces[] = self::selectById($id);}
            catch(EntryNotFoundException $e){}
        }

        return $workspaces;
    }

    /**
     * @param int $workspace
     * @return Team[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectByWorkspace(int $workspace): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Tickets_Team` WHERE `id` IN (SELECT `team` FROM `Tickets_Workspace_Team` WHERE `workspace` = ?)');
        $select->bindParam(1, $workspace, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $teams = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$teams[] = self::selectById($id);}
            catch(EntryNotFoundException $e){}
        }

        return $teams;
    }

    /**
     * @param string $name
     * @return Team
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $name): Team
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `Tickets_Team` (`name`) VALUES (:name)');
        $insert->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $name
     * @return Team
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $name): Team
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `Tickets_Team` SET `name` = :name WHERE `id` = :id');
        $update->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_STR);
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

        $delete = $handler->prepare('DELETE FROM `Tickets_Team` WHERE `id` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @return User[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectUsers(int $id): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `User` WHERE `id` IN (SELECT `user` FROM `Tickets_Team_User` WHERE `team` = ?)');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $users = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $userId)
        {
            try{$users[] = UserOperator::getUser($userId);}
            catch(EntryNotFoundException $e){}
        }

        return $users;
    }

    /**
     * @param string $name
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function nameInUse(string $name): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Tickets_Team` WHERE `name` = ? LIMIT 1');
        $select->bindParam(1, $name, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param array $users
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function setUsers(int $id, array $users): int
    {
        $count = 0;

        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `Tickets_Team_User` WHERE `team` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $insert = $handler->prepare('INSERT INTO `Tickets_Team_User` (`team`, `user`) VALUES (:team, :user)');
        $insert->bindParam('team', $id, DatabaseConnection::PARAM_INT);

        foreach($users as $user)
        {
            try
            {
                $insert->bindParam('user', $user, DatabaseConnection::PARAM_INT);
                $insert->execute();
                $count++;
            }
            catch(DatabaseException $e){}
        }

        $handler->close();

        return $count;
    }

    /**
     * @param int $team
     * @param int $user
     * @return bool
     * @throws DatabaseException
     */
    public static function userInTeam(int $team, int $user): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `user` FROM `Tickets_Team_User` WHERE `user` = :user AND `team` = :team LIMIT 1');
        $select->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $select->bindParam('team', $team, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }
}
