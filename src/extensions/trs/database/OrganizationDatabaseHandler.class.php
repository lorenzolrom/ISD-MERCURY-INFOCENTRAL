<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/08/2020
 * Time: 2:54 PM
 */


namespace extensions\trs\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use database\PreparedStatement;
use exceptions\EntryNotFoundException;
use extensions\trs\models\Organization;

class OrganizationDatabaseHandler extends DatabaseHandler
{
    /**
     * Select a single Organization by ID
     * @param int $id Numerical ID of the Organization
     * @return Organization
     * @throws EntryNotFoundException The ID was not found
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Organization
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT `id`, `name`, `type`, `phone`, `email`, `street`, `city`, `state`, `zip`, `approved` FROM `TRS_Organization` WHERE `id` = ? LIMIT 1');
        $s->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $s->execute();

        $c->close();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $s->fetchObject('extensions\trs\models\Organization');
    }

    /**
     * @param string $name
     * @param array $type
     * @param string $phone
     * @param string $email
     * @param string $street
     * @param string $city
     * @param string $state
     * @param string $zip
     * @param array $approved
     * @return Organization[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $name, array $type, string $phone, string $email, string $street, string $city,
                                  string $state, string $zip, array $approved): array
    {
        $c = new DatabaseConnection();

        // Default query with single value fields
        $q = "SELECT `id` FROM `TRS_Organization` WHERE `name` LIKE :name AND `phone` LIKE :phone AND `email` 
                                  LIKE :email AND `street` LIKE :street AND `city` LIKE :city AND `state` LIKE :state 
                                      AND `zip` LIKE :zip";

        /**
         * Begin add multi-value fields to query
         */
        if(is_array($type) AND !empty($type))
        {
            $finalTypes = array();

            // Filter valid types
            foreach($type as $typeCode)
            {
                if(in_array($typeCode, Organization::TYPES))
                    $finalTypes[] = $typeCode;
            }

            $q .= " AND `type` IN ('" . implode("', '", $finalTypes) . "')";
        }

        if(is_array($approved) AND !empty($approved))
        {
            $q .= " AND `approved` IN (" . self::getBooleanString($approved) . ")";
        }
        /*
         * End add multi-value fields to query
         */

        $s = $c->prepare($q);
        $s->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $s->bindParam('phone', $phone, DatabaseConnection::PARAM_STR);
        $s->bindParam('email', $email, DatabaseConnection::PARAM_STR);
        $s->bindParam('street', $street, DatabaseConnection::PARAM_STR);
        $s->bindParam('state', $state, DatabaseConnection::PARAM_STR);
        $s->bindParam('city', $city, DatabaseConnection::PARAM_STR);
        $s->bindParam('zip', $zip, DatabaseConnection::PARAM_STR);
        $s->execute();

        $c->close();

        $organizations = array();

        foreach($s->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $orgID)
        {
            try
            {
                $organizations[] = self::selectById((int)$orgID);
            }
            catch(EntryNotFoundException $e){} // Ignore invalid orgID, this should not occur
        }

        return $organizations;
    }

    /**
     * @param array $args Assoc array of all parameters and their values
     * @return Organization
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(array $args): Organization
    {
        $c = new DatabaseConnection();

        $i = $c->prepare(PreparedStatement::buildQueryString('TRS_Organization', PreparedStatement::INSERT, Organization::FIELDS));

        $i->bindParams($args);

        $i->execute();

        $id = $c->getLastInsertId();


        $c->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param array $args
     * @return Organization
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, array $args): Organization
    {
        $c = new DatabaseConnection();

        $u = $c->prepare(PreparedStatement::buildQueryString('TRS_Organization', PreparedStatement::UPDATE, Organization::FIELDS));

        $u->bindParams(array_merge($args, array('id' => $id)));

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

        $d = $c->prepare('DELETE FROM `TRS_Organization` WHERE `id` = ?');
        $d->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $d->execute();

        $c->close();

        return $d->getRowCount() === 1;
    }
}