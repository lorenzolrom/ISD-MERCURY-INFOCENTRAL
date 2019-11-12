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

class Lock extends Model
{
    private $id;
    private $system;
    private $code;

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
    public function getSystem(): int
    {
        return $this->system;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }
}