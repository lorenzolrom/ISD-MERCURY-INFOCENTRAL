<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/13/2019
 * Time: 11:14 AM
 */


namespace extensions\knowledgebase\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\knowledgebase\models\Collection;

class CollectionDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Collection
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     */
    public static function selectById(int $id): Collection
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `name` FROM `KB_Collection` WHERE `id` = ? LIMIT 1');
        $s->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $s->execute();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        $c->close();

        return $s->fetchObject('extensions\knowledgebase\models\Collection');
    }

    /**
     * @param $name
     * @return Collection
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert($name): Collection
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `KB_Collection` (`name`) VALUES (:name)');
        $i->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $i->execute();

        $id = $c->getLastInsertId();

        $c->close();

        return self::selectById($id);
    }

    /**
     * @param $id
     * @param $name
     * @return Collection
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update($id, $name): Collection
    {
        $c = new DatabaseConnection();

        $u = $c->prepare('UPDATE `KB_Collection` SET `name` = :name WHERE `id` = :id');
        $u->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $u->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $u->execute();

        $c->close();

        return self::selectById($id);
    }

    /**
     * @param $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete($id): bool
    {
        $c = new DatabaseConnection();

        $d = $c->prepare('DELETE FROM `KB_Collection` WHERE `id` = ?');
        $d->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $d->execute();

        $c->close();

        return $d->getRowCount() === 1;
    }
}