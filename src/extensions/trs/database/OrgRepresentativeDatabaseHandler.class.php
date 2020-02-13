<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/11/2020
 * Time: 10:11 AM
 */


namespace extensions\trs\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use database\PreparedStatement;

class OrgRepresentativeDatabaseHandler extends DatabaseHandler
{
    private const FIELDS = array('organization', 'user');

    /**
     * @param int $organization Org ID
     * @param int $user User ID
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $organization, int $user): bool
    {
        $c = new DatabaseConnection();

        $i = $c->prepare(PreparedStatement::buildQueryString('TRS_Organization_Representative', PreparedStatement::INSERT, self::FIELDS));
        $i->bindParams(array(
            'organization' => $organization,
            'user' => $user
        ));

        $i->execute();

        $c->close();

        return $i->getRowCount();
    }

    /**
     * @param int $organization
     * @param int $user
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $organization, int $user): bool
    {
        $c = new DatabaseConnection();

        $d = $c->prepare('DELETE FROM `TRS_Organization_Representative` WHERE `organization` = :organization AND `user` = :user');
        $d->bindParam('organization', $organization, DatabaseConnection::PARAM_INT);
        $d->bindParam('user', $user, DatabaseConnection::PARAM_INT);
        $d->execute();

        $c->close();

        return $d->getRowCount() === 1;
    }

    /**
     * @param int $organization
     * @return int[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(int $organization): array
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `user` FROM `TRS_Organization_Representative` WHERE `organization` = ?');
        $s->bindParam(1, $organization, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        return $s->fetchAll(DatabaseConnection::FETCH_COLUMN, 0);
    }
}