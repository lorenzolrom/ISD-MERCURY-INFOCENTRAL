<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/18/2019
 * Time: 12:18 PM
 */


namespace messages;


class ValidationError
{
    // Codes
    const VALUE_IS_OK = 0;
    const VALUE_ALREADY_TAKEN = 1;
    const VALUE_IS_TOO_SHORT = 2;
    const VALUE_IS_TOO_LONG = 3;
    const VALUE_IS_NULL = 4;

    // Messages
    const MESSAGE_VALUE_ALREADY_TAKEN = "Already In Use";
    const MESSAGE_VALUE_REQUIRED = "Is Required";
    const MESSAGE_VALUE_LENGTH_INVALID = "Must Be Between {{@bound1}} And {{@bound2}} Characters";
    const MESSAGE_VALUE_ALREADY_ASSIGNED = "Object Already Assigned";
    const MESSAGE_VALUE_NOT_FOUND = "Not Found";
    const MESSAGE_VALUE_NOT_ASSIGNED = "Object Not Assigned";

    /**
     * @param int $lowerBound Least number of acceptable characters
     * @param int $upperBound Greatest number of acceptable characters
     * @return string
     */
    public static function getLengthMessage(int $lowerBound, int $upperBound): string
    {
        $baseMessage = self::MESSAGE_VALUE_LENGTH_INVALID;
        $baseMessage = str_replace("{{@bound1}}", $lowerBound, $baseMessage);
        $baseMessage = str_replace("{{@bound2}}", $upperBound, $baseMessage);

        return $baseMessage;
    }
}