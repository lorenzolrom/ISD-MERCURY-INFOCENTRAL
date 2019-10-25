<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 10/18/2019
 * Time: 12:19 PM
 */


namespace database\forms;


use database\DatabaseConnection;
use database\DatabaseHandler;
use exceptions\EntryNotFoundException;
use models\forms\Field;

class FieldDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Field
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function select(int $id): Field
    {
        $d = new DatabaseConnection();

        $select = $d->prepare('SELECT `id`, `form`, `sequence`, `private`, `type`, `title`, `placeholder`, 
            `required`, `validation` FROM `Forms_Field` WHERE `id` = ? LIMIT 1');
        $select->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $select->execute();

        if($select->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        $d->close();

        return $select->fetchObject('models\forms\Field');
    }

    /**
     * @param int $formId
     * @return Field[]
     * @throws \exceptions\DatabaseException
     */
    public static function selectByForm(int $formId): array
    {
        $d = new DatabaseConnection();

        $select = $d->prepare('SELECT `id`, `form`, `sequence`, `private`, `type`, `title`, `placeholder`, 
            `required`, `validation` FROM `Forms_Field` WHERE `form` = ? ORDER BY `sequence` DESC');
        $select->bindParam(1, $formId, DatabaseConnection::PARAM_INT);
        $select->execute();

        $d->close();

        return $select->fetchAll(DatabaseConnection::FETCH_CLASS, 'models\forms\Field');
    }

    /**
     * @param int $form INT(11) UNSIGNED
     * @param int $sequence INT(11) UNSIGNED
     * @param int $private TINYINT(1) UNSIGNED
     * @param string $type ENUM('text', 'select', 'upload')
     * @param string $title VARCHAR(64)
     * @param string|null $placeholder TEXT
     * @param int $required TINYINT(1) UNSIGNED
     * @param int|null $validation INT(11) UNSIGNED
     * @return Field
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $form, int $sequence, int $private, string $type, string $title,
                                  ?string $placeholder, int $required, ?int $validation): Field
    {
        $d = new DatabaseConnection();

        $insert = $d->prepare('INSERT INTO `Forms_Field`(`form`, `sequence`, `private`, `type`, `title`, 
                          `placeholder`, `required`, `validation`) VALUES (:form, :sequence, :private, :type, :title, 
                                                                           :placeholder, :required, :validation)');

        $insert->bindParam('form', $form, DatabaseConnection::PARAM_INT);
        $insert->bindParam('sequence', $sequence, DatabaseConnection::PARAM_INT);
        $insert->bindParam('private', $private, DatabaseConnection::PARAM_INT);
        $insert->bindParam('type', $type, DatabaseConnection::PARAM_STR);
        $insert->bindParam('title', $title, DatabaseConnection::PARAM_STR);
        $insert->bindParam('placeholder', $placeholder, DatabaseConnection::PARAM_STR);
        $insert->bindParam('required', $required, DatabaseConnection::PARAM_INT);
        $insert->bindParam('validation', $validation, DatabaseConnection::PARAM_INT);
        $insert->execute();

        $id = $d->getLastInsertId();

        $d->close();

        return self::select((int)$id);
    }

    public static function update(): Field
    {
        // TODO implement
        return new Field();
    }

    public static function delete(): bool
    {
        // TODO implement
        return false;
    }
}