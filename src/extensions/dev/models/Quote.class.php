<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 12:26 PM
 */


namespace extensions\dev\models;


class Quote
{
    private $id;
    private $content;

    /**
     * Quote constructor.
     * @param int $id
     * @param string $content
     */
    public function __construct(int $id, string $content)
    {
        $this->id = $id;
        $this->content = $content;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }


}