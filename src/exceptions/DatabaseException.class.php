<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 10:49 AM
 */


namespace exceptions;


use Throwable;

class DatabaseException extends \Exception
{
    const FAILED_TO_CONNECT = 1;
    const DIRECT_QUERY_FAILED = 2;
    const PREPARED_QUERY_FAILED = 3;
    const TRANSACTION_START_FAILED = 4;
    const TRANSACTION_COMMIT_FAILED = 5;
    const TRANSACTION_ROLLBACK_FAILED = 6;

    private $sqlCode;

    /**
     * DatabaseException constructor.
     * @param string $message
     * @param int $code
     * @param int $sqlCode
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, $sqlCode = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->sqlCode = $sqlCode;
    }

    /**
     * @return int SQL error code
     */
    public function getSQLCode(): int
    {
        return $this->sqlCode;
    }
}