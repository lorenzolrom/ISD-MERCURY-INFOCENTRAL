<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/12/2020
 * Time: 4:08 PM
 */


namespace extensions\trs\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\trs\models\CommodityCategory;

class CommodityCategoryDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return CommodityCategory
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): CommodityCategory
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `name` FROM `TRS_CommodityCategory` WHERE `id` = ? LIMIT 1');
        $s->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\trs\models\CommodityCategory');
    }

    /**
     * @param string $name
     * @return CommodityCategory[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $name): array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `name` FROM `TRS_CommodityCategory` WHERE `name` LIKE ?');
        $s->bindParam(1, $name, DatabaseConnection::PARAM_STR);
        $s->execute();

        $c->close();

        return $s->fetchAll(DatabaseConnection::FETCH_CLASS, 'extensions\trs\models\CommodityCategory');
    }

    /**
     * @param string $name
     * @return CommodityCategory
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $name): CommodityCategory
    {
        $c = new DatabaseConnection();

        $i = $c->prepare('INSERT INTO `TRS_CommodityCategory` (`name`) VALUES (:name)');
        $i->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $i->execute();

        $id = $c->getLastInsertId();

        $c->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $name
     * @return CommodityCategory
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $name): CommodityCategory
    {
        $c = new DatabaseConnection();

        $u = $c->prepare('UPDATE `TRS_CommodityCategory` SET `name` = :name WHERE `id` = :id');
        $u->bindParams(array(
            'name' => $name,
            'id' => $id
        ));

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

        $d = $c->prepare('DELETE FROM `TRS_CommodityCategory` WHERE `id` = ?');
        $d->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $d->execute();

        $c->close();

        return $d->getRowCount() === 1;
    }
}