<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 11:08 AM
 */


namespace exceptions;


class SecurityException extends \Exception
{
    const APPTOKEN_NOT_SUPPLIED = 301;
    const APPTOKEN_NOT_FOUND = 302;
    const APPTOKEN_NO_PERMISSION_FOR_ROUTE = 303;

    const USER_LOGINNAME_NOT_FOUND = 304;
    const USER_PASSWORD_IS_WRONG = 305;

    const USERTOKEN_NOT_SUPPLIED = 306;
    const USERTOKEN_NOT_FOUND = 307;

    const USER_NOT_AUTHENTICATED = 308;
    const USER_NO_PERMISSION = 309;

    const SECRET_NO_PERMISSION = 310;
}