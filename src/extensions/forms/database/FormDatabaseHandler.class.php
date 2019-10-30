<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 10/18/2019
 * Time: 12:18 PM
 */


namespace extensions\forms\database;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use extensions\forms\models\Form;

class FormDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int|null $id ID of a single form.  If left blank, all forms will be selected.
     * @return Form[]|Form If $id is not specified, an array of Form will be returned, if it is, a single Form will be returned
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function select(?int $id = null)
    {
        $d = new DatabaseConnection();

        $query = 'SELECT `id`, `title`, `owner`, `active`, emailRequired, `sendConfirmationEmail` FROM `Forms_Form` ';

        if($id !== NULL)
        {
            $query .= 'WHERE `id` = ? LIMIT 1';
        }

        $select = $d->prepare($query);

        if($id !== NULL) // id specified, located single record
        {
            $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
            $select->execute();
            $d->close();

            if($select->getRowCount() !== 1)
                throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

            return $select->fetchObject('extensions\forms\models\Form');
        }
        else
        {
            $select->execute();
            $d->close();

            return $select->fetchAll(DatabaseConnection::FETCH_CLASS, 'extensions\forms\models\Form');
        }
    }

    /**
     * @param string $title TEXT
     * @param int $owner INT(11)
     * @param int $active TINYINT(1) UNSIGNED
     * @param int $emailRequired TINYINT(1) UNSIGNED
     * @param int $sendConfirmationEmail TINYINT(1) UNSIGNED
     * @return Form
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(string $title, int $owner, int $active, int $emailRequired, int $sendConfirmationEmail): Form
    {
        $d = new DatabaseConnection();

        $insert = $d->prepare('INSERT INTO `Forms_Form`(`title`, `owner`, `active`, `emailRequired`, 
                         `sendConfirmationEmail`) VALUES (:title, :owner, :active, :emailRequired, :sendConfirmationEmail)');
        $insert->bindParam('title', $title, DatabaseConnection::PARAM_STR);
        $insert->bindParam('owner', $owner, DatabaseConnection::PARAM_INT);
        $insert->bindParam('active', $active, DatabaseConnection::PARAM_INT);
        $insert->bindParam('emailRequired', $emailRequired, DatabaseConnection::PARAM_INT);
        $insert->bindParam('sendConfirmationEmail', $sendConfirmationEmail, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $id = $d->getLastInsertId();

        $d->close();

        return self::select((int)$id);
    }

    /**
     * @param int $id
     * @param string $title TEXT
     * @param int $owner INT(11)
     * @param int $active TINYINT(1) UNSIGNED
     * @param int $emailRequired TINYINT(1) UNSIGNED
     * @param int $sendConfirmationEmail TINYINT(1) UNSIGNED
     * @return Form
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, string $title, int $owner, int $active, int $emailRequired, int $sendConfirmationEmail): Form
    {
        $d = new DatabaseConnection();

        $update = $d->prepare('UPDATE `Forms_Form` SET `title` = :title, `owner` = :owner, `active` = :active, 
                        `emailRequired` = :emailRequired, `sendConfirmationEmail` = :sendConfirmationEmail WHERE `id` = :id');

        $update->bindParam('title', $title, DatabaseConnection::PARAM_STR);
        $update->bindParam('owner', $owner, DatabaseConnection::PARAM_INT);
        $update->bindParam('active', $active, DatabaseConnection::PARAM_INT);
        $update->bindParam('emailRequired', $emailRequired, DatabaseConnection::PARAM_INT);
        $update->bindParam('sendConfirmationEmail', $sendConfirmationEmail, DatabaseConnection::PARAM_INT);
        $update->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $update->execute();

        $d->close();

        return self::select($id);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function delete(int $id): bool
    {
        $d = new DatabaseConnection();

        $delete = $d->prepare('DELETE FROM `Forms_Form` WHERE `id` = ?');
        $delete->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $delete->execute();

        $d->close();

        return $delete->getRowCount() === 0;
    }
}