<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 6:00 PM
 */


namespace exceptions;


class TokenException extends \Exception
{
    const ALREADY_EXPIRED = 501;
    const HAS_EXPIRED = 502;
}