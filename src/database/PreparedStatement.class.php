<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 4:03 PM
 */


namespace database;


use exceptions\DatabaseException;

class PreparedStatement
{
    private $statement; // Stored SQL query statement

    /**
     * PreparedStatement.class constructor.
     * @param \PDO $handler A DatabaseConnection object
     * @param string $query SQL query string
     */
    public function __construct(\PDO $handler, string $query)
    {
        $this->statement = $handler->prepare($query);
    }

    /**
     * @param mixed $index Either numeric indicator or string substitution
     * @param mixed $parameter Parameter to bind
     * @param int $parameterType Type of parameter to bind
     */
    public function bindParam($index, $parameter, int $parameterType)
    {
        $this->statement->bindParam($index, $parameter, $parameterType);
    }

    /**
     * @param mixed $index Either numeric indicator or string substitution
     * @param mixed $value Value to be bound
     */
    public function bindValue($index, $value)
    {
        $this->statement->bindValue($index, $value);
    }

    /**
     * @return bool Execution succeeded
     * @throws DatabaseException If execution fails
     */
    public function execute(): bool
    {
        try
        {
            $this->statement->execute();
            return TRUE;
        }
        catch(\PDOException $e)
        {
            throw new DatabaseException(DatabaseException::MESSAGES[DatabaseException::PREPARED_QUERY_FAILED], DatabaseException::PREPARED_QUERY_FAILED, $e);
        }
    }

    /**
     * @return array Next row in results
     */
    public function fetch(): array
    {
        return $this->statement->fetch();
    }

    /**
     * @param mixed $fetchType Option for how data should be returned
     * @param mixed $fetchArgument Arguments for fetchType
     * @return array Array of row results
     */
    public function fetchAll($fetchType = FALSE, $fetchArgument = 0): array
    {
        if($fetchType !== FALSE)
            return $this->statement->fetchAll($fetchType, $fetchArgument);
        else
            return $this->statement->fetchAll();
    }

    /**
     * @return mixed
     */
    public function fetchColumn()
    {
        return $this->statement->fetchColumn();
    }

    /**
     * @return int Count of returned rows
     */
    public function getRowCount(): int
    {
        return $this->statement->rowCount();
    }

    /**
     * @param string $className
     * @return mixed
     */
    public function fetchObject(string $className)
    {
        return $this->statement->fetchObject($className);
    }
}