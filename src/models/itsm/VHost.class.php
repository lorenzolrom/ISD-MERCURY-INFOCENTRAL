<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 5:21 PM
 */


namespace models\itsm;


class VHost
{
    const STATUS_ATTRIBUTE_TYPE = "wdns";

    private $id;
    private $domain;
    private $subdomain;
    private $name;
    private $host;
    private $registrar;
    private $status;
    private $renewCost;
    private $notes;
    private $registerDate;
    private $expireDate;
    private $createDate;
    private $createUser;
    private $modifyDate;
    private $modifyUser;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getSubdomain(): string
    {
        return $this->subdomain;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getHost(): int
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getRegistrar(): int
    {
        return $this->registrar;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return float
     */
    public function getRenewCost(): float
    {
        return $this->renewCost;
    }

    /**
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @return string
     */
    public function getRegisterDate(): string
    {
        return $this->registerDate;
    }

    /**
     * @return string|null
     */
    public function getExpireDate(): ?string
    {
        return $this->expireDate;
    }

    /**
     * @return string
     */
    public function getCreateDate(): string
    {
        return $this->createDate;
    }

    /**
     * @return int
     */
    public function getCreateUser(): int
    {
        return $this->createUser;
    }

    /**
     * @return string
     */
    public function getModifyDate(): string
    {
        return $this->modifyDate;
    }

    /**
     * @return int
     */
    public function getModifyUser(): int
    {
        return $this->modifyUser;
    }

}