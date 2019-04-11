<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 5:05 PM
 */


namespace database\itsm;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use models\itsm\VHost;

class VHostDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return VHost
     *
     * Selects basic information about a VHost needed for display
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     */
    public static function selectById(int $id): VHost
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT id, domain, id, domain, subdomain, name, host, registrar, status, renewCost, notes, registerDate, expireDate, createDate, createUser, modifyDate, modifyUser FROM ITSM_VHost WHERE id = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("models\itsm\VHost");
    }

    /**
     * @param string $domain
     * @param string $subdomain
     * @param string $name
     * @param string $assetTag
     * @param string $registrarCode
     * @param mixed $status
     * @return VHost[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $domain = "%", string $subdomain = "%", string $name = "%", string $assetTag = "%",
                                  string $registrarCode = "%", $status = array()): array
    {
        $query = "SELECT id FROM ITSM_VHost WHERE domain LIKE :domain AND subdomain LIKE :subdomain AND name LIKE :name 
                            AND host IN (SELECT id FROM ITSM_Host WHERE asset IN (SELECT id FROM ITSM_Asset WHERE assetTag LIKE :assetTag) 
                            AND registrar IN (SELECT id FROM ITSM_Registrar WHERE code LIKE :registrarCode))";

        // Add status filter, if it is supplied
        if(is_array($status) AND !empty($status))
        {
            $query .= " AND status IN (SELECT id FROM Attribute WHERE extension = 'itsm' AND type='wdns' AND code IN (" . self::getAttributeCodeString($status) . "))";
        }

        $query .= "ORDER BY domain, subdomain ASC";

        $handler = new DatabaseConnection();

        $select = $handler->prepare($query);

        $select->bindParam('domain', $domain, DatabaseConnection::PARAM_STR);
        $select->bindParam('subdomain', $subdomain, DatabaseConnection::PARAM_STR);
        $select->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $select->bindParam('registrarCode', $registrarCode, DatabaseConnection::PARAM_STR);
        $select->bindParam('assetTag', $assetTag, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $vhosts = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $vhosts[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){} // ignore
        }

        return $vhosts;
    }
}