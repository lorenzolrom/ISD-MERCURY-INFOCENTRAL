<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 4:13 PM
 */


namespace exceptions;


use Throwable;

class ControllerNotFoundException extends MercuryException
{
    const CONTROLLER_NOT_FOUND = 201;

    const MESSAGES = array(
        self::CONTROLLER_NOT_FOUND => "Controller Not Found"
    );

    public function __construct(string $controller, int $code = self::CONTROLLER_NOT_FOUND, Throwable $previous = null)
    {
        parent::__construct(self::MESSAGES[$code] . ": $controller", $code, $previous);
    }
}