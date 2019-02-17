<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 11:01 AM
 */


namespace exceptions;


class EntryNotFoundException extends \Exception
{
    const PRIMARY_KEY_NOT_FOUND = 101;
    const UNIQUE_KEY_NOT_FOUND = 102;
}