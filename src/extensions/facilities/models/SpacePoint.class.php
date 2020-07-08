<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 11/04/2019
 * Time: 10:30 AM
 */


namespace extensions\facilities\models;


use models\Model;

/**
 * A point in the poly-line that will be constructed for this space
 * Class SpacePoint
 * @package extensions\facilities\models
 */
class SpacePoint extends Model
{
    private $id;
    private $space; // Reference to 'location' in Space
    private $pD; // Percent of the way down this point is on the floorplan
    private $pR; // Percent of the way right this point is on the floorplan

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
    public function getSpace(): int
    {
        return $this->space;
    }

    /**
     * @return float
     */
    public function getPD(): float
    {
        return $this->pD;
    }

    /**
     * @return float
     */
    public function getPR(): float
    {
        return $this->pR;
    }


}
