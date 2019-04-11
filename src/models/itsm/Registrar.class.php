<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/06/2019
 * Time: 10:33 AM
 */


namespace models\itsm;


use models\Model;

class Registrar extends Model
{
    private $id;
    private $code;
    private $name;
    private $url;
    private $phone;
    private $createDate;
    private $createUser;
    private $lastModifyDate;
    private $lastModifyUser;

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
    public function getCode(): string
    {
        return $this->code;
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
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
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
    public function getLastModifyDate(): string
    {
        return $this->lastModifyDate;
    }

    /**
     * @return int
     */
    public function getLastModifyUser(): int
    {
        return $this->lastModifyUser;
    }


}