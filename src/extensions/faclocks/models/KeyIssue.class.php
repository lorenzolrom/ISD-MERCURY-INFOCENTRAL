<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/08/2019
 * Time: 10:56 AM
 */


namespace extensions\faclocks\models;


use models\Model;

class KeyIssue extends Model
{
    private $id;
    private $key;
    private $issue;
    private $user;
    private $notes;

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
    public function getKey(): int
    {
        return $this->key;
    }

    /**
     * @return int
     */
    public function getIssue(): int
    {
        return $this->issue;
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
    public function getNotes(): string
    {
        return $this->notes;
    }


}