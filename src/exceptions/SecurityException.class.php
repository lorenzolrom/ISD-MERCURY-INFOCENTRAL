<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 4:22 PM
 */


namespace exceptions;


class SecurityException extends MercuryException
{
    const KEY_NOT_SUPPLIED = 501;
    const KEY_NOT_FOUND = 502;
    const KEY_NO_PERMISSION = 503;
    const USER_NOT_FOUND = 504;
    const USER_PASSWORD_INCORRECT = 505;
    const USER_IS_DISABLED = 506;
    const AUTHENTICATION_REQUIRED = 507;
    const TOKEN_EXPIRED = 508;
    const USER_NO_PERMISSION = 509;

    const MESSAGES = array(
        self::KEY_NOT_SUPPLIED => "An API key was not supplied with the request",
        self::KEY_NOT_FOUND => "An API key was supplied, but was not found",
        self::KEY_NO_PERMISSION => "An API key was supplied, but it is not allowed to perform the requested action",
        self::USER_NOT_FOUND => "Username or password is incorrect",
        self::USER_PASSWORD_INCORRECT => "Username or password is incorrect",
        self::USER_IS_DISABLED => "Username or password is incorrect",
        self::AUTHENTICATION_REQUIRED => "Please sign in",
        self::TOKEN_EXPIRED => "Session expired",
        self::USER_NO_PERMISSION => "You do not have permission to perform this action"
    );
}