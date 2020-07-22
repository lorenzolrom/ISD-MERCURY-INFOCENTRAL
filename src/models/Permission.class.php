<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
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
