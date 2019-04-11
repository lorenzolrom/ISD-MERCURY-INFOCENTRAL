<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 4:21 PM
 */


namespace exceptions;


class EntryNotFoundException extends MercuryException
{
    const PRIMARY_KEY_NOT_FOUND = 301;
    const UNIQUE_KEY_NOT_FOUND = 302;
    const FOREIGN_KEY_NOT_FOUND = 303;

    const MESSAGES = array(
        self::PRIMARY_KEY_NOT_FOUND => "Requested resource was not found",
        self::UNIQUE_KEY_NOT_FOUND => "Requested resource was not found",
        self::FOREIGN_KEY_NOT_FOUND => "Requested resource was not found"
    );
}