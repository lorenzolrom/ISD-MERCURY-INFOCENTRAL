<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/17/2019
 * Time: 12:35 PM
 */


namespace extensions\faclocks\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\faclocks\models\Door;

class DoorDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Door
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Door
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `location`, `description` FROM `FacilitiesLock_Door` WHERE `id` = ? LIMIT 1');
        $s->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\faclocks\models\Door');
    }

    /**
     * @param int $location
     * @param string $description
     * @return Door
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $location, string $description): Door
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `FacilitiesLock_Door`(`location`, `description`) VALUES (:location, :description)');
        $i->bindParam('location', $location, DatabaseConnection::PARAM_INT);
        $i->bindParam('description', $description, DatabaseConnection::PARAM_STR);
        $i->execute();

        $id = $c->getLastInsertId();

        $c->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $description
     * @return Door
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $description): Door
    {
        $c = new DatabaseConnection();

        $u = $c->prepare('UPDATE `FacilitiesLock_Door` SET `description` = :description WHERE `id` = :id');
        $u->bindParam('description', $description, DatabaseConnection::PARAM_STR);
        $u->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $u->execute();

        $c->close();
        return self::selectById($id);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $c = new DatabaseConnection();

        $d = $c->prepare('DELETE FROM `FacilitiesLock_Door` WHERE `id` = ?');
        $d->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $d->execute();

        $c->close();

        return $d->getRowCount() === 1;
    }
}