<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 12/17/2019
 * Time: 1:07 PM
 */


namespace extensions\netc_network\models;


use models\Model;

/**
 * Class NetInterface
 *
 * A network interface on a Computer
 * The database entity is called 'Interface', this class is named NetInterface because
 * of restrictions on class names in P.H.P.
 *
 * @package extensions\netc_network\models
 */
class NetInterface extends Model
{
    private $macAddress; // Primary key
    private $computer; // Key of Computer
    private $label;
    private $type; // IP, Subnet, or Roamer
    private $typeData; // Only set if type is IP (IP assignment) or Subnet (PK of subnet)

    // Unique key (computer, label)

    /**
     * @return string
     */
    public function getMacAddress(): string
    {
        return $this->macAddress;
    }

    /**
     * @return int
     */
    public function getComputer(): int
    {
        return $this->computer;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getTypeData(): ?string
    {
        return $this->typeData;
    }


}
