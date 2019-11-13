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
use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use extensions\knowledgebase\models\Article;

class ArticleDatabaseHandler extends DatabaseHandler
{
    /**
     * @param int $id
     * @return Article
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function selectById(int $id): Article
    {
        $c = new DatabaseConnection();

        $s = $c->prepare('SELECT * FROM `KB_Article` WHERE `id` = ? LIMIT 1');
        $s->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $s->execute();

        if($s->getRowCount() !== 1)
            throw new EntryNotFoundException(EntryNotFoundException::MESSAGES[EntryNotFoundException::PRIMARY_KEY_NOT_FOUND], EntryNotFoundException::PRIMARY_KEY_NOT_FOUND);

        $c->close();

        return $s->fetchObject('extensions\knowledgebase\models\Article');
    }

    /**
     * @param int $collection
     * @param string $author
     * @param string $status
     * @param string $title
     * @param string $summary
     * @param string $errorMessage
     * @param string $cause
     * @param string $solution
     * @param string $details
     * @param string $symptoms
     * @return Article
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function insert(int $collection, string $author, string $status, string $title, string $summary,
                                  string $errorMessage, string $cause, string $solution, string $details, string $symptoms): Article
    {
        $c = new DatabaseConnection();
        $c->startTransaction(); // Part of this INSERT queries for the next available ID, both queries must succeed

        try
        {
            $i = $c->prepare('INSERT INTO `KB_Article` (`id`, `collection`, `author`, `status`, `title`, `summary`, 
                          `errorMessage`, `cause`, `solution`, `details`, `symptoms`) VALUES (:id, :collection, :author, 
                          :status, :title, :summary, :errorMessage, :cause, :solution, :details, :symptoms)');

            $i->bindParam('id', self::selectNextID($c), DatabaseConnection::PARAM_INT); // Get the next available ID
            $i->bindParam('collection', $collection, DatabaseConnection::PARAM_INT);
            $i->bindParam('author', $author, DatabaseConnection::PARAM_STR);
            $i->bindParam('status', $status, DatabaseConnection::PARAM_STR);
            $i->bindParam('title', $title, DatabaseConnection::PARAM_STR);
            $i->bindParam('summary', $summary, DatabaseConnection::PARAM_STR);
            $i->bindParam('errorMessage', $errorMessage, DatabaseConnection::PARAM_STR);
            $i->bindParam('cause', $cause, DatabaseConnection::PARAM_STR);
            $i->bindParam('solution', $solution, DatabaseConnection::PARAM_STR);
            $i->bindParam('details', $details, DatabaseConnection::PARAM_STR);
            $i->bindParam('symptoms', $symptoms, DatabaseConnection::PARAM_STR);
            $i->execute();

            $id = $c->getLastInsertId();
        }
        catch(DatabaseException $e)
        {
            $c->rollback();
            $c->close();
            throw $e;
        }

        $c->commit();
        $c->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @param int $collection
     * @param string $author
     * @param string $status
     * @param string $title
     * @param string $summary
     * @param string $errorMessage
     * @param string $cause
     * @param string $solution
     * @param string $details
     * @param string $symptoms
     * @return Article
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function update(int $id, int $collection, string $author, string $status, string $title, string $summary,
                                  string $errorMessage, string $cause, string $solution, string $details, string $symptoms): Article
    {
        $c = new DatabaseConnection();

        $u = $c->prepare('UPDATE `KB_Article` SET `collection` = :collection, `author` = :author, 
                        `status` = :status, `title` = :title, `summary` = :summary, `errorMessage` = :errorMessage, 
                        `cause` = :cause, `solution` = :solution, `details` = :details, `symptoms` = :symptoms WHERE `id` = :id');

        $u->bindParam('id', $id, DatabaseConnection::PARAM_INT);
        $u->bindParam('collection', $collection, DatabaseConnection::PARAM_INT);
        $u->bindParam('author', $author, DatabaseConnection::PARAM_STR);
        $u->bindParam('status', $status, DatabaseConnection::PARAM_STR);
        $u->bindParam('title', $title, DatabaseConnection::PARAM_STR);
        $u->bindParam('summary', $summary, DatabaseConnection::PARAM_STR);
        $u->bindParam('errorMessage', $errorMessage, DatabaseConnection::PARAM_STR);
        $u->bindParam('cause', $cause, DatabaseConnection::PARAM_STR);
        $u->bindParam('solution', $solution, DatabaseConnection::PARAM_STR);
        $u->bindParam('details', $details, DatabaseConnection::PARAM_STR);
        $u->bindParam('symptoms', $symptoms, DatabaseConnection::PARAM_STR);
        $u->execute();

        $c->close();

        return self::selectById($id);
    }

    /**
     * @param int $id
     * @return bool
     * @throws DatabaseException
     */
    public static function delete(int $id): bool
    {
        $c = new DatabaseConnection();

        $d = $c->prepare('DELETE FROM `KB_Article` WHERE `id` = ?');
        $d->bindParam(1, $id, DatabaseConnection::PARAM_INT);
        $d->execute();

        $c->close();

        return $d->getRowCount() === 1;
    }

    /**
     * Selects the next available KB Article ID
     * @param DatabaseConnection $c
     * @return int
     * @throws \exceptions\DatabaseException
     */
    private static function selectNextID(DatabaseConnection $c): int
    {
        $s = $c->prepare('SELECT `id` FROM `KB_Article` ORDER BY `id` DESC LIMIT 1');
        $s->execute();

        if($s->getRowCount() !== 1)
            return 0;

        return (int)$s->fetchColumn();
    }
}