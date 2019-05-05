<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/28/2019
 * Time: 12:19 PM
 */


namespace database;


use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use models\Bulletin;
use models\Role;

class BulletinDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Bulletin
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Bulletin
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id`, `startDate`, `endDate`, `title`, `message`, `inactive`, `type` FROM `Bulletin` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('models\Bulletin');
    }

    /**
     * @param string $startDate
     * @param string $endDate
     * @param string $title
     * @param string $message
     * @param array $inactive
     * @return Bulletin[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $startDate = '1000-01-01', string $endDate = '9999-12-31', string $title = '%', string $message = '%', ?array $inactive = NULL): array
    {
        $query = 'SELECT `id` FROM `Bulletin` WHERE `startDate` >= :startDate AND `endDate` <= :endDate AND `title` LIKE :title AND `message` LIKE :message';

        if($inactive !== NULL)
            $query .= ' AND `inactive` IN (' . self::getBooleanString($inactive) . ')';

        $query .= ' ORDER BY endDate, startDate DESC';

        $handler = new DatabaseConnection();

        $select = $handler->prepare($query);
        $select->bindParam('startDate', $startDate, DatabaseConnection::PARAM_STR);
        $select->bindParam('endDate', $endDate, DatabaseConnection::PARAM_STR);
        $select->bindParam('title', $title, DatabaseConnection::PARAM_STR);
        $select->bindParam('message', $message, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $bulletins = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$bulletins[] = self::selectById($id);}
            catch(EntryNotFoundException $e){}
        }

        return $bulletins;
    }

    /**
     * @param int $userId
     * @return Bulletin[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectActiveByUser(int $userId): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `Bulletin` WHERE CURDATE() < `endDate` AND `inactive` = 0 
                              AND `id` IN (SELECT `bulletin` FROM `Role_Bulletin` WHERE `role` IN (SELECT `role` FROM `User_Role` WHERE User_Role.`user` LIKE ?)) ORDER BY `startDate` ASC');
        $select->bindParam(1, $userId, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $bulletins = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try{$bulletins[] = self::selectById($id);}
            catch(EntryNotFoundException $e){}
        }

        return $bulletins;
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `Bulletin` WHERE `id` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param string $startDate
     * @param string $endDate
     * @param string $title
     * @param string $message
     * @param int $inactive
     * @param string $type
     * @return Bulletin
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $startDate, string $endDate, string $title, string $message, int $inactive,
                                  string $type): Bulletin
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `Bulletin` (startDate, endDate, title, message, inactive, type) 
            VALUES (:startDate, :endDate, :title, :message, :inactive, :type)');

        $insert->bindParam('startDate', $startDate, DatabaseConnection::PARAM_STR);
        $insert->bindParam('endDate', $endDate, DatabaseConnection::PARAM_STR);
        $insert->bindParam('title', $title, DatabaseConnection::PARAM_STR);
        $insert->bindParam('message', $message, DatabaseConnection::PARAM_STR);
        $insert->bindParam('inactive', $inactive, DatabaseConnection::PARAM_INT);
        $insert->bindParam('type', $type, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $startDate
     * @param string $endDate
     * @param string $title
     * @param string $message
     * @param int $inactive
     * @param string $type
     * @return Bulletin
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $startDate, string $endDate, string $title, string $message,
                                  int $inactive, string $type): Bulletin
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `Bulletin` SET `startDate` = :startDate, `endDate` = :endDate, 
                      `title` = :title, `message` = :message, `inactive` = :inactive, `type` = :type WHERE `id` = :id');

        $update->bindParam('startDate', $startDate, DatabaseConnection::PARAM_STR);
        $update->bindParam('endDate', $endDate, DatabaseConnection::PARAM_STR);
        $update->bindParam('title', $title, DatabaseConnection::PARAM_STR);
        $update->bindParam('message', $message, DatabaseConnection::PARAM_STR);
        $update->bindParam('inactive', $inactive, DatabaseConnection::PARAM_INT);
        $update->bindParam('type', $type, DatabaseConnection::PARAM_STR);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @return Role[]
     * @throws \exceptions\DatabaseException
     */
    public static function getRoles(int $id): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `role` FROM `Role_Bulletin` WHERE `bulletin` = ?');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        $roles = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $roleId)
        {
            try{$roles[] = RoleDatabaseHandler::selectById($roleId);}
            catch(EntryNotFoundException $e){}
        }

        return $roles;
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

        // Remove existing roles
        $delete = $handler->prepare('DELETE FROM `Role_Bulletin` WHERE `bulletin` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $select = $handler->prepare('INSERT INTO `Role_Bulletin` (role, bulletin) VALUES (:role, :bulletin)');
        $select->bindParam('bulletin', $id, DatabaseConnection::PARAM_INT);

        $count = 0;

        foreach($roles as $role)
        {
            try
            {
                $select->bindParam('role', $role, DatabaseConnection::PARAM_INT);
                $select->execute();
            }
            catch(DatabaseException $e){}
        }

        $handler->close();

        return $count;
    }
}