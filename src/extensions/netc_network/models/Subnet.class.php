<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 12/17/2019
 * Time: 1:06 PM
 */


namespace extensions\netc_network\models;


use models\Model;

/**
 * Class Subnet
 *
 * A range of IP addresses (defined by IP and NetMask)
 *
 * @package extensions\netc_network\models
 */
class Subnet extends Model
{
    private $ip; // Primary Key
    private $location;
    private $netmask;

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
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
    public function getNetmask(): string
    {
        return $this->netmask;
    }


}