<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 4:07 PM
 */


namespace models;

/**
 * Class HTTPResponse
 *
 * A response from the API to the client
 *
 * @package models
 */
class HTTPResponse
{
    const OK = 200;
    const CREATED = 201;
    const NO_CONTENT = 204;

    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const CONFLICT = 409;

    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;

    const NOT_IMPLEMENTED_MESSAGE = array('notice' => 'This feature has not been implemented...yet');

    private $responseCode;
    private $body;

    /**
     * HTTPResponse constructor.
     * @param int $responseCode
     * @param array $body
     * @param bool $errors
     */
    public function __construct(int $responseCode, array $body = array(), bool $errors = FALSE)
    {
        $this->responseCode = $responseCode;

        if($errors)
            $this->body = array('errors' => $body);

        $this->body = $body;
    }

    /**
     * @return int
     */
    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }


}