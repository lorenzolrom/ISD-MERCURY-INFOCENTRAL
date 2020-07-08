<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 12:51 PM
 */


namespace models;


class Permission extends Model
{
    private $code;

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }
}
