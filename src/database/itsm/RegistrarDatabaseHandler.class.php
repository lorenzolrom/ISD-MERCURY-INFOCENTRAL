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

        $select = $handler->prepare("SELECT `id`, `code`, `name`, `url`, `phone` FROM `ITSM_Registrar` WHERE `id` = ? LIMIT 1");
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

        $select = $handler->prepare("SELECT `id` FROM `ITSM_Registrar` WHERE `code` LIKE :code AND `name` LIKE :name ORDER BY `code` ASC");
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

    /**
     * @param string $code
     * @param string $name
     * @param string $url
     * @param string $phone
     * @return Registrar
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $code, string $name, string $url, string $phone): Registrar
    {
        $handler = new DatabaseConnection();

        $insert = $handler->prepare('INSERT INTO `ITSM_Registrar` (`code`, `name`, `url`, `phone`) VALUES (:code, :name, :url, :phone)');
        $insert->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $insert->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $insert->bindParam('url', $url, DatabaseConnection::PARAM_STR);
        $insert->bindParam('phone', $phone, DatabaseConnection::PARAM_STR);
        $insert->execute();

        $id = $handler->getLastInsertId();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param string $code
     * @param string $name
     * @param string $url
     * @param string $phone
     * @return Registrar
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $code, string $name, string $url, string $phone): Registrar
    {
        $handler = new DatabaseConnection();

        $update = $handler->prepare('UPDATE `ITSM_Registrar` SET `code` = :code, `name` = :name, `url` = :url, `phone` = :phone WHERE `id` = :id');
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->bindParam('code', $code, DatabaseConnection::PARAM_STR);
        $update->bindParam('name', $name, DatabaseConnection::PARAM_STR);
        $update->bindParam('url', $url, DatabaseConnection::PARAM_STR);
        $update->bindParam('phone', $phone, DatabaseConnection::PARAM_STR);
        $update->execute();

        $handler->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $handler = new DatabaseConnection();

        $delete = $handler->prepare('DELETE FROM `ITSM_Registrar` WHERE `id` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $handler->close();

        return $delete->getRowCount() === 1;
    }

    /**
     * @param string $code
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function codeInUse(string $code): bool
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `id` FROM `ITSM_Registrar` WHERE `code` = ? LIMIT 1');
        $select->bindParam(1, $code, DatabaseConnection::PARAM_STR);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1;
    }

    /**
     * @param int $id
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function selectNameById(int $id): ?string
    {
        $handler = new DatabaseConnection();

        $select = $handler->prepare('SELECT `name` FROM `ITSM_Registrar` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        $handler->close();

        return $select->getRowCount() === 1 ? $select->fetchColumn() : NULL;
    }
}