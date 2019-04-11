<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 10:24 PM
 */


namespace models\itsm;


use models\Model;

class Application extends Model
{
    private $id;
    private $number;
    private $name;
    private $description;
    private $owner;
    private $type;
    private $status;
    private $publicFacing;
    private $lifeExpectancy;
    private $dataVolume;
    private $authType;
    private $port;
    private $createUser;
    private $createDate;
    private $lastModifyUser;
    private $lastModifyDate;

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
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getOwner(): int
    {
        return $this->owner;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getPublicFacing(): int
    {
        return $this->publicFacing;
    }

    /**
     * @return int
     */
    public function getLifeExpectancy(): int
    {
        return $this->lifeExpectancy;
    }

    /**
     * @return int
     */
    public function getDataVolume(): int
    {
        return $this->dataVolume;
    }

    /**
     * @return int
     */
    public function getAuthType(): int
    {
        return $this->authType;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
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
    public function getCreateDate(): string
    {
        return $this->createDate;
    }

    /**
     * @return int
     */
    public function getLastModifyUser(): int
    {
        return $this->lastModifyUser;
    }

    /**
     * @return string
     */
    public function getLastModifyDate(): string
    {
        return $this->lastModifyDate;
    }


}