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


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use extensions\tickets\models\Workspace;

class WorkspaceDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Workspace
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Workspace
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id`, `name`, `requestPortal` FROM `Tickets_Workspace` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('extensions\tickets\models\Workspace');
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function select(): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Tickets_Workspace`');
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
     * @param string $name
     * @return Workspace
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $name): Workspace
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `Tickets_Workspace` (`name`) VALUES (:name)');
        $insert->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $name
     * @return Workspace
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $name): Workspace
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `Tickets_Workspace` SET `name` = :name WHERE `id` = :id');
        $update->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
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

        $delete = $handler->prepare('DELETE FROM `Tickets_Workspace` WHERE `id` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param string $name
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function nameInUse(string $name): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Tickets_Workspace` WHERE `name` = ? LIMIT 1');
        $select->bindParam(1, $name, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function hasTickets(int $id): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Tickets_Ticket` WHERE `workspace` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @param array $teams
     * @return int
     * @throws DatabaseException
     */
    public static function setTeams(int $id, array $teams): int
    {
        $count = 0;

        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `Tickets_Workspace_Team` WHERE `workspace` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $insert = $handler->prepare('INSERT INTO `Tickets_Workspace_Team` (`team`, `workspace`) VALUES (:team, :workspace)');
        $insert->bindParam('workspace', $id, DatabaseConnection::PARAM_INT);

        foreach($teams as $team)
        {
            try
            {
                $insert->bindParam('team', $team, DatabaseConnection::PARAM_INT);
                $insert->execute();
                $count++;
            }
            catch(DatabaseException $e){}
        }

        $handler->close();

        return $count;
    }

    /**
     * @param int $workspace
     * @param int $user
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function userInWorkspace(int $workspace, int $user): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `User` WHERE `id` IN (SELECT `user` FROM `Tickets_Team_User` 
            WHERE `user` = :user AND `team` IN (SELECT `team` FROM `Tickets_Workspace_Team` WHERE `workspace` = :workspace)) LIMIT 1');
        $select->bindParam('workspace', $workspace, DatabaseConnection::PARAM_INT);
        $select->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $workspace
     * @param int $team
     * @return bool
     * @throws DatabaseException
     */
    public static function teamInWorkspace(int $workspace, int $team): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `team` FROM `Tickets_Workspace_Team` WHERE `workspace` = :workspace AND `team` = :team LIMIT 1');
        $select->bindParam('workspace', $workspace, DatabaseConnection::PARAM_INT);
        $select->bindParam('team', $team, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $workspace
     * @return bool
     * @throws DatabaseException
     */
    public static function setRequestPortal(int $workspace): bool
    {
        $handler = new DatabaseConnection();
        
        $update = $handler->prepare('UPDATE `Tickets_Workspace` SET `requestPortal` = 0 WHERE `requestPortal` = 1');
        $update->execute();

        $update = $handler->prepare('UPDATE `Tickets_Workspace` SET `requestPortal` = 1 WHERE `id` = ?');
        $update->bindParam(1, $workspace, DatabaseConnection::PARAM_INT);
        $update->execute();
        
        $handler->close();

        return $update->getRowCount();
    }

    /**
     * @return Workspace
     * @throws DatabaseException
     * @throws EntryNotFoundException
     */
    public static function selectRequestPortal(): Workspace
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Tickets_Workspace` WHERE `requestPortal` = 1 LIMIT 1');
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException('No Request Portal has been defined', EntryNotFoundException::UNIQUE_KEY_NOT_FOUND);

        return self::selectById((int)$select->fetchColumn());
    }

    /**
     * Add a Secret (API Key) to the list of allowed Secrets for the specified workspace
     *
     * @param int $workspaceID The ID of the workspace, unsigned int
     * @param int $secretID The ID of the secret, unsigned int
     * @return bool True if the Secret was added, or was already present, false if it could not be added
     * @throws DatabaseException
     */
    public static function addSecret(int $workspaceID, int $secretID): bool
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `Tickets_Workspace_Secret`(`workspace`, `secret`) VALUES (:workspace, :secret)');
        $i->bindParam('workspace', $workspaceID, DatabaseConnection::PARAM_INT);
        $i->bindParam('secret', $secretID, DatabaseConnection::PARAM_INT);
        $i->execute();

        $c->close();

        return $i->getRowCount() === 1;
    }

    /**
     * Remove a Secret (API Key) from the list of API Keys allowed to interact with the specified workspace
     *
     * @param int $workspaceID Numerical ID of the workspace
     * @param int $secretID The ID of the Secret
     * @return bool Was the Secret removed?
     * @throws DatabaseException
     */
    public static function delSecret(int $workspaceID, int $secretID): bool
    {
        $c = new DatabaseConnection();

        $d = $c->prepare('DELETE FROM `Tickets_Workspace_Secret` WHERE `workspace` = :workspace AND `secret` = :secret');
        $d->bindParam('workspace', $workspaceID, DatabaseConnection::PARAM_INT);
        $d->bindParam('secret', $secretID, DatabaseConnection::PARAM_INT);
        $d->execute();

        $c->close();

        return $d->getRowCount();
    }

    /**
     * @param int $workspaceID
     * @return array
     * @throws DatabaseException
     */
    public static function getAllowedSecrets(int $workspaceID): array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `secret` FROM `Tickets_Workspace_Secret` WHERE `workspace` = ?');
        $s->bindParam(1, $workspaceID, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        return $s->fetchAll(DatabaseConnection::FETCH_COLUMN, 0);
    }

    /**
     * Is the supplied Secret allowed to interact with the specified workspace?
     *
     * @param int $workspaceID Numerical Workspace ID
     * @param int $secretID Secret ID
     * @return bool
     * @throws DatabaseException
     */
    public static function isSecretAllowed(int $workspaceID, int $secretID): bool
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `workspace` FROM `Tickets_Workspace_Secret` WHERE `workspace` = :workspace AND `secret` = :secret LIMIT 1');
        $s->bindParam('workspace', $workspaceID, DatabaseConnection::PARAM_INT);
        $s->bindParam('secret', $secretID, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        return $s->getRowCount() === 1;
    }
}
