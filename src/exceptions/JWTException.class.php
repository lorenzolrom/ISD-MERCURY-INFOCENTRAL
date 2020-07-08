<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
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
