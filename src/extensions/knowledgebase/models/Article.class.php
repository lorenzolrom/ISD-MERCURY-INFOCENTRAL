<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/13/2019
 * Time: 11:08 AM
 */


namespace extensions\knowledgebase\models;


use models\Model;

class Article extends Model
{
    private $id;
    private $collection;
    private $author;
    private $status;
    private $title;
    private $summary;
    private $errorMessage;
    private $cause;
    private $solution;
    private $details;
    private $symptoms;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getCollection(): int
    {
        return $this->collection;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getSummary(): string
    {
        return $this->summary;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @return string
     */
    public function getCause(): string
    {
        return $this->cause;
    }

    /**
     * @return string
     */
    public function getSolution(): string
    {
        return $this->solution;
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return $this->details;
    }

    /**
     * @return string
     */
    public function getSymptoms(): string
    {
        return $this->symptoms;
    }




}