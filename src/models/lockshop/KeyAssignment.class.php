<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/24/2019
 * Time: 12:05 PM
 */


namespace models\lockshop;


use models\Model;

class KeyAssignment extends Model
{
    private $key;
    private $user;
    private $serial;

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
    public function getUser(): int
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getSerial(): string
    {
        return $this->serial;
    }


}