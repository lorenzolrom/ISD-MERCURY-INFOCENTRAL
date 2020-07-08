<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 5:07 PM
 */


namespace models;


abstract class Model
{
    /**
     * Return this object as an array.
     * Only Public attributes will be shown
     * @return array
     */
    public function toArray(): array
    {
        return json_decode(json_encode($this), TRUE);
    }
}
