<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 11/04/2019
 * Time: 10:38 AM
 */


namespace extensions\facilities\database;


use database\DatabaseConnection;
use exceptions\EntryNotFoundException;
use extensions\facilities\models\SpacePoint;

class SpacePointDatabaseHandler
{
    /**
     * @param int $space
     * @return SpacePoint[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectBySpace(int $space): array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `space`, `pD`, `pR` FROM `Facilities_SpacePoint` WHERE `space` = ?');
        $s->bindParam(1, $space, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        return $s->fetchAll(DatabaseConnection::FETCH_CLASS, 'extensions\facilities\models\SpacePoint');
    }

    /**
     * @param int $id
     * @return SpacePoint
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): SpacePoint
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `space`, `pD`, `pR` FROM `Facilities_SpacePoint` WHERE `id` = ? LIMIT 1');
        $s->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\facilities\models\SpacePoint');
    }

    /**
     * @param int $space
     * @param float $pD
     * @param float $pR
     * @param DatabaseConnection|null $conn If this is supplied, it will be used as the data connection.  Intended for transactions.
     * @return SpacePoint
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $space, float $pD, float $pR, ?DatabaseConnection $conn = NULL)
    {
        if($conn === NULL)
            $c = new DatabaseConnection();
        else
            $c = $conn;

        $i = $c->prepare('INSERT INTO `Facilities_SpacePoint`(`space`, `pD`, `pR`) VALUES (:space, :pD, :pR)');
        $i->bindParam('space', $space, DatabaseConnection::PARAM_INT);
        $i->bindParam('pD', $pD, DatabaseConnection::PARAM_STR);
        $i->bindParam('pR', $pR, DatabaseConnection::PARAM_STR);
        $i->execute();

        $id = $c->getLastInsertId();

        if($conn === NULL)
            $c->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id)
    {
        $c = new DatabaseConnection();

        $d = $c->prepare('DELETE FROM `Facilities_SpacePoint` WHERE `id` = ?');
        $d->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $d->execute();

        $c->close();

        return $d->getRowCount() === 1;
    }

    /**
     * Deletes all points in the provided Space
     * @param int $spaceId
     * @param DatabaseConnection|null $conn If this is supplied, it will be used as the data connection.  Intended for transactions.
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function deleteBySpace(int $spaceId, ?DatabaseConnection $conn = NULL): bool
    {
        if($conn === NULL)
            $c = new DatabaseConnection();
        else
            $c = $conn;

        $d = $c->prepare('DELETE FROM `Facilities_SpacePoint` WHERE `space` = ?');
        $d->bindParam(1, $spaceId, DatabaseConnection::PARAM_INT);
        $d->execute();

        if($conn === NULL)
            $c->close();

        return $d->getRowCount() !== 0;
    }
}
