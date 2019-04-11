<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 4:04 PM
 */


namespace exceptions;


class DatabaseException extends MercuryException
{
    // Error Codes
    const FAILED_TO_CONNECT = 101;
    const DIRECT_QUERY_FAILED = 102;
    const PREPARED_QUERY_FAILED = 103;
    const TRANSACTION_START_FAILED = 104;
    const TRANSACTION_COMMIT_FAILED = 105;
    const TRANSACTION_ROLLBACK_FAILED = 106;

    // Error Messages
    const MESSAGES = [
        self::FAILED_TO_CONNECT => "Could Not Establish Connection To Database",
        self::DIRECT_QUERY_FAILED => "Query Failed To Execute (Direct)",
        self::PREPARED_QUERY_FAILED => "Query Failed To Execute (Prepared)",
        self::TRANSACTION_START_FAILED => "Transaction Failed To Start",
        self::TRANSACTION_COMMIT_FAILED => "Transaction Failed To Commit",
        self::TRANSACTION_ROLLBACK_FAILED => "Transaction Failed To Rollback"
    ];
}