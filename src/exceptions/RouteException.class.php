<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 5:59 PM
 */


namespace exceptions;


class RouteException extends MercuryException
{
    const REQUIRED_PARAMETER_IS_INVALID = 401;
    const REQUEST_INVALID = 402;
    const FILE_REQUIRED = 403;

    const MESSAGES = array(
        self::REQUIRED_PARAMETER_IS_INVALID => "A required URI parameter was not supplied, or is invalid",
        self::REQUEST_INVALID => "No suitable functions were found to handle the request or request method",
        self::FILE_REQUIRED => "A required file was not provided with the request"
    );
}