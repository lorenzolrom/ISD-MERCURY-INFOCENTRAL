<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/10/2019
 * Time: 10:08 PM
 */


namespace models\itsm;


use models\Model;

abstract class ITSMModel extends Model
{
    protected $createDate;
    protected $createUser;
    protected $lastModifyDate;
    protected $lastModifyUser;

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