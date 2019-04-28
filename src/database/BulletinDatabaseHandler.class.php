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


use exceptions\EntryNotFoundException;
use models\Bulletin;

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

        $select = $handler->prepare('SELECT `id`, `user`, `startDate`, `endDate`, `title`, `message`, `inactive`, `type` FROM `Bulletin` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject('models\Bulletin');
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
}