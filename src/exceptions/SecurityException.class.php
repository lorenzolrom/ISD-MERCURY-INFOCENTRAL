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
}