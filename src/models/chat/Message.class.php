<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 10/21/2019
 * Time: 4:19 PM
 */


namespace models\chat;


use models\Model;

class Message extends Model
{
    private $id;
    private $user;
    private $room;
    private $message;
    private $time;

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
     * @return int
     */
    public function getRoom(): int
    {
        return $this->room;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->time;
    }
}