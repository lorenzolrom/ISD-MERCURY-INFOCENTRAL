<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/12/2020
 * Time: 2:17 PM
 */


namespace extensions\cliff\models;


use models\Model;

class KeyIssue extends Model
{
    public $id;
    public $key;
    public $serial;
    public $issuedTo;

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
    public function getSerial(): int
    {
        return $this->serial;
    }

    /**
     * @return string
     */
    public function getIssuedTo(): string
    {
        return $this->issuedTo;
    }


}