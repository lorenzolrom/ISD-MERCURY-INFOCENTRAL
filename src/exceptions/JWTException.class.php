<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
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
