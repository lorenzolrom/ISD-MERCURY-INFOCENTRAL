<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 4:16 PM
 */


namespace controllers;


use exceptions\EntryInUseException;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use models\HTTPRequest;
use models\HTTPResponse;

abstract class Controller
{
    protected $request;

    /**
     * Controller constructor.
     * @param HTTPRequest $request
     */
    public function __construct(HTTPRequest $request)
    {
        $this->request = $request;
    }

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws ValidationError
     * @throws EntryNotFoundException
     * @throws EntryInUseException
     */
    abstract public function getResponse(): ?HTTPResponse;

    /**
     * Returns the body of the request with unset values defined as '%', and wildcards added if they are enabled
     *
     * @param array $fields
     * @param bool $strict
     * @return array
     */
    public function getFormattedBody(array $fields, bool $strict = TRUE): array
    {
        $args = $this->request->body();

        foreach($fields as $field)
        {
            if(!isset($args[$field]))
            {
                if(!$strict)
                    $args[$field] = '%';
                else
                    $args[$field] = null;
            }
            else
            {
                if(!$strict)
                    $args[$field] = self::wildcard($args[$field]);
            }
        }

        return $args;
    }

    /**
     * Adds wildcards to the supplied variable
     *
     * @param mixed $subject
     * @return string|array
     */
    public static function wildcard($subject)
    {
        if(is_array($subject))
            return $subject;

        return '%' . $subject . '%';
    }
}