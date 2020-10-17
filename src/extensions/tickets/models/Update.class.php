<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/13/2019
 * Time: 4:45 PM
 */


namespace extensions\tickets\models;


use models\Model;

class Update extends Model
{
    private $id;
    private $ticket;
    private $user;
    private $time;
    private $description;

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
    public function getTicket(): int
    {
        return $this->ticket;
    }

    public function getUser(): int
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }


}
