<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * MERCURY InfoCentral
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 10:49 AM
 */


namespace exceptions;


class DatabaseException extends \Exception
{
    const FAILED_TO_CONNECT = 1;
    const DIRECT_QUERY_FAILED = 2;
    const PREPARED_QUERY_FAILED = 3;
    const TRANSACTION_START_FAILED = 4;
    const TRANSACTION_COMMIT_FAILED = 5;
    const TRANSACTION_ROLLBACK_FAILED = 6;
}