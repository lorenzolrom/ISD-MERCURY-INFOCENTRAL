<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 11:41 AM
 */


namespace exceptions;


class RouteException extends \Exception
{
    const ROUTE_NOT_SUPPLIED = 401;
    const ROUTE_URI_NOT_FOUND = 402;
    const REQUIRED_PARAMETER_MISSING = 403;
    const REQUIRED_PARAMETER_IS_INVALID = 404;
}