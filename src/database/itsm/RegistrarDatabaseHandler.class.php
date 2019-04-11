<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/06/2019
 * Time: 10:35 AM
 */


namespace database\itsm;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use models\itsm\Registrar;

class RegistrarDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Registrar
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Registrar
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT id, code, name, url, phone, createDate, createUser, lastModifyDate, lastModifyUser FROM ITSM_Registrar WHERE id = ? LIMIT 1");
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        return $select->fetchObject("models\itsm\Registrar");
    }

    /**
     * @param string $code
     * @param string $name
     * @return Registrar[]
     * @throws \exceptions\DatabaseException
     */
    public static function select(string $code = '%', string $name = '%'): array
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare("SELECT id FROM ITSM_Registrar WHERE code LIKE :code AND name LIKE :name ORDER BY code ASC");
        $select->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $select->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        $results = array();

        foreach($select->fetchAll(DatabaseConnection::FETCH_COLUMN, 0) as $id)
        {
            try
            {
                $results[] = self::selectById($id);
            }
            catch(EntryNotFoundException $e){}
        }

        return $results;
    }
}