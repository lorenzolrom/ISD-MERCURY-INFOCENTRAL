<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/15/2020
 * Time: 2:50 PM
 */


namespace exceptions;


class JWTException extends SecurityException
{
    const JWT_PAYLOAD_INVALID = 1401;

    const MESSAGES = array(
        self::JWT_PAYLOAD_INVALID => 'JWT could not be read'
    );
}
