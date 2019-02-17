<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 10:48 AM
 */


namespace messages;


class Messages
{
    const DATABASE_FAILED_TO_CONNECT = "Could Not Connect To Database";
    const DATABASE_DIRECT_QUERY_FAILED = "Direct Query Failure";
    const DATABASE_PREPARED_QUERY_FAILED = "";
    const DATABASE_TRANSACTION_START_FAILED = "Failed To Begin Transaction";
    const DATABASE_TRANSACTION_COMMIT_FAILED = "Failed To Commit Transaction";
    const DATABASE_TRANSACTION_ROLLBACK_FAILED = "Failed To Rollback Transaction";

    const CONTROLLER_NOT_FOUND = "Controller Not Found";

    const ROUTE_NOT_SUPPLIED = "Route Not Supplied";
    const ROUTE_URI_NOT_FOUND = "Requested Route U.R.I. Not Found";

    const SECURITY_APPTOKEN_NOT_SUPPLIED = "Application Token Not Supplied";
    const SECURITY_APPTOKEN_NOT_FOUND = "Application Token Not Found";
    const SECURITY_APPTOKEN_NO_PERMISSION_FOR_ROUTE = "Application Token Does Not Have Permission For Requested Route";
}