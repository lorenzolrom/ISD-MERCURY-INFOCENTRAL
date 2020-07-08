<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 12/06/2019
 * Time: 10:32 AM
 */


namespace extensions\tickets\models;


use models\Model;

/**
 *
 * Class Lock
 * An instance of a user actively editing a Ticket, preventing other users from updating it
 * @package extensions\tickets\models
 */
class Lock extends Model
{
    private $ticket;
    private $user;
    private $active;
    private $lastCheckin;

    /**
     * @return int
     */
    public function getTicket(): int
    {
        return $this->ticket;
    }

    /**
     * @return int
     */
    public function getUser(): int
    {
        return $this->user;
    }

    /**
     * @return int
     */
    public function getActive(): int
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getLastCheckin(): string
    {
        return $this->lastCheckin;
    }
}
