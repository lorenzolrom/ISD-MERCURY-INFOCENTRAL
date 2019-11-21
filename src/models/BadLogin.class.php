<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/21/2019
 * Time: 10:22 AM
 */


namespace models;

/**
 * Meta-data of a failed login attempt
 *
 * Class BadLogin
 * @package models
 */
class BadLogin extends Model
{
    private $time;
    private $username;
    private $suppliedIP;
    private $sourceIP;

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
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getSuppliedIP(): string
    {
        return $this->suppliedIP;
    }

    /**
     * @return string
     */
    public function getSourceIP(): string
    {
        return $this->sourceIP;
    }


}