<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 9:02 AM
 */


namespace models;

/**
 * Class Token
 *
 * User session token
 *
 * @package models
 */
class Token
{
    private $token;
    private $user;
    private $issueTime;
    private $expireTime;
    private $expired;
    private $ipAddress;

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
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
    public function getIssueTime(): string
    {
        return $this->issueTime;
    }

    /**
     * @return string
     */
    public function getExpireTime(): string
    {
        return $this->expireTime;
    }

    /**
     * @return int
     */
    public function getExpired(): int
    {
        return $this->expired;
    }

    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }


}
