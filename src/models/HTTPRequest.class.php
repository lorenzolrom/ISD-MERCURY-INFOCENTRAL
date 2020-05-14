<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 4:18 PM
 */


namespace models;

/**
 * Class HTTPRequest
 *
 * A request from the client
 *
 * @package models
 */
class HTTPRequest
{
    const GET = "GET";
    const POST = "POST";
    const PUT = "PUT";
    const DELETE = "DELETE";

    private $method;
    private $uriParts;
    private $body;

    /**
     * HTTPRequest constructor.
     * @param string $method HTTP request method
     * @param array $uriParts Route (after base URI) being requested
     * @param array|null $body
     */
    public function __construct(string $method, array $uriParts, ?array $body = NULL)
    {
        $this->method = $method;
        $this->body = $body;
        $this->uriParts = $uriParts;
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * @return string|null
     */
    public function next(): ?string
    {
        return array_shift($this->uriParts);
    }

    /**
     * @return array|null
     */
    public function body(): ?array
    {
        return $this->body;
    }


}
