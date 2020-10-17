<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
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
