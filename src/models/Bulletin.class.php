<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/28/2019
 * Time: 12:16 PM
 */


namespace models;


class Bulletin extends Model
{
    private $id;
    private $user;
    private $startDate;
    private $endDate;
    private $title;
    private $message;
    private $inactive;
    private $type;

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
    public function getUser(): int
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * @return string
     */
    public function getEndDate(): string
    {
        return $this->endDate;
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
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getInactive(): int
    {
        return $this->inactive;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

}