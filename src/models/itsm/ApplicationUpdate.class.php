<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/03/2019
 * Time: 5:38 PM
 */


namespace models\itsm;


use models\Model;

class ApplicationUpdate extends Model
{
    private $id;
    private $application;
    private $status;
    private $time;
    private $user;
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
    public function getApplication(): int
    {
        return $this->application;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->time;
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
    public function getDescription(): string
    {
        return $this->description;
    }


}