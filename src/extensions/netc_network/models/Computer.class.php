<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 12/17/2019
 * Time: 1:06 PM
 */


namespace extensions\netc_network\models;


use models\Model;

/**
 * Class Computer
 *
 * A physical computer device registered on the network
 *
 * @package extensions\netc_network\models
 */
class Computer extends Model
{
    private $id; // Primary key
    private $label;
    private $active;
    private $user;
    private $tags;
    private $operatingSystems;
    private $notes;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return int
     */
    public function getActive(): int
    {
        return $this->active;
    }

    /**
     * @return string | null Username of the user assigned to this device, if one is available
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getTags(): string
    {
        return $this->tags;
    }

    /**
     * @return string
     */
    public function getOperatingSystems(): string
    {
        return $this->operatingSystems;
    }

    /**
     * @return string
     */
    public function getNotes(): string
    {
        return $this->notes;
    }
}
