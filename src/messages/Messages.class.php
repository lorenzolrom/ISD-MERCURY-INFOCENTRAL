<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * MERCURY InfoCentral
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
    const DATABASE_PREPARED_QUERY_FAILED = "Prepared Query Failure";
    const DATABASE_TRANSACTION_START_FAILED = "Failed To Begin Transaction";
    const DATABASE_TRANSACTION_COMMIT_FAILED = "Failed To Commit Transaction";
    const DATABASE_TRANSACTION_ROLLBACK_FAILED = "Failed To Rollback Transaction";

    const CONTROLLER_NOT_FOUND = "Controller Not Found";

    const ROUTE_NOT_SUPPLIED = "Route Not Supplied";
    const ROUTE_URI_NOT_FOUND = "Requested Route U.R.I. Not Found";
    const ROUTE_REQUIRED_PARAMETER_MISSING = "Required Parameter Missing";
    const ROUTE_REQUIRED_PARAMETER_IS_INVALID = "Required Parameter Is Invalid";

    const SECURITY_APPTOKEN_NOT_SUPPLIED = "Application Token Not Supplied";
    const SECURITY_APPTOKEN_NOT_FOUND = "Application Token Not Found";
    const SECURITY_APPTOKEN_NO_PERMISSION_FOR_ROUTE = "Application Token Does Not Have Permission For Requested Route";
    const SECURITY_SECRET_DOES_NOT_HAVE_PERMISSION = "Application Does Not Have Permission";

    const USER_NOT_FOUND = "Username or Password Is Incorrect";
    const USER_PASSWORD_IS_WRONG = "Username or Password Is Incorrect";
    const USER_LOGGED_OUT = "User Logged Out";

    const SECURITY_USERTOKEN_NOT_SUPPLIED = "User Token Not Supplied";
    const SECURITY_USERTOKEN_NOT_FOUND = "User Token Not Found";
    const SECURITY_NO_AUTHENTICATED_USER = "Please Sign In";
    const SECURITY_USER_DOES_NOT_HAVE_PERMISSION = "You Do Not Have Permission";

    const USERTOKEN_ALREADY_EXPIRED = "Token Already Expired";
    const USERTOKEN_TOKEN_HAS_EXPIRED = "Token Has Expired";

    const ROLE_NOT_FOUND = "Role Not Found";

    const PERMISSION_NOT_FOUND = "Permission Not Found";
}