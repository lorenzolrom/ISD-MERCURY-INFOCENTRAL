<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/03/2019
 * Time: 11:47 AM
 */


namespace exceptions;


class UploadException extends \Exception
{
    public const MOVE_UPLOADED_FILE_FAILED = 1301;

    public const MESSAGES = array(
        self::MOVE_UPLOADED_FILE_FAILED => 'File could not be moved to final destination'
    );
}