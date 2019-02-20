<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 3:31 PM
 */


namespace models;


use database\TokenDatabaseHandler;

class Token extends Model
{
    private $token;
    private $user;
    private $issueTime;
    private $expireTime;
    private $expired;
    private $ipAddress;

    /**
     * UserToken constructor.
     * @param string $token
     * @param int $user
     * @param string $issueTime
     * @param string $expireTime
     * @param int $expired
     * @param string $ipAddress
     */
    public function __construct(string $token, int $user, string $issueTime, string $expireTime, int $expired, string $ipAddress)
    {
        $this->token = $token;
        $this->user = $user;
        $this->issueTime = $issueTime;
        $this->expireTime = $expireTime;
        $this->expired = $expired;
        $this->ipAddress = $ipAddress;
    }

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

    /**
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\TokenException
     */
    public function expire()
    {
        TokenDatabaseHandler::expireToken($this->token);
    }
}