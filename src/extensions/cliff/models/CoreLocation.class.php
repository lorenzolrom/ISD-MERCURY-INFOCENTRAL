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
 * Time: 2:16 PM
 */


namespace extensions\cliff\models;


use models\Model;

class CoreLocation extends Model
{
    public $id;
    public $core;
    public $building;
    public $location;
    public $notes;

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
    public function getCore(): int
    {
        return $this->core;
    }

    /**
     * @return string
     */
    public function getBuilding(): string
    {
        return $this->building;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @return string
     */
    public function getNotes(): string
    {
        return $this->notes;
    }


}
