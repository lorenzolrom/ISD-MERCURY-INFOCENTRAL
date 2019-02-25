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
    const VALUE_IS_INVALID = 5;

    // Messages
    const MESSAGE_VALUE_ALREADY_TAKEN = "Already In Use";
    const MESSAGE_VALUE_REQUIRED = "Is Required";
    const MESSAGE_VALUE_LENGTH_INVALID = "Must Be Between {{@bound1}} And {{@bound2}} Characters";
    const MESSAGE_VALUE_ALREADY_ASSIGNED = "Object Already Assigned";
    const MESSAGE_VALUE_NOT_FOUND = "Not Found";
    const MESSAGE_VALUE_NOT_ASSIGNED = "Object Not Assigned";
    const MESSAGE_VALUE_NOT_VALID = "Not Valid";
    const MESSAGE_PASSWORD_TOO_SHORT = "Must Be Greater Than 8 Characters";

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

    /**
     * Generates an array that can be added to the "errors" returned by a POST/PUT operation
     * @param string $field Name of form/table field
     * @param string $message Error message
     * @return array
     */
    public static function getErrorArrayEntry(string $field, string $message): array
    {
        return ['type' => 'validation', 'field' => $field, 'message' => $message];
    }
}