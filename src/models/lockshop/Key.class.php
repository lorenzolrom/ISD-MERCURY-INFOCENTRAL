<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/24/2019
 * Time: 11:24 AM
 */


namespace models\lockshop;


use models\Model;

class Key extends Model
{
    private $id;
    private $system;
    private $bitting;
    private $quantity;

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
    public function getSystem(): int
    {
        return $this->system;
    }

    /**
     * @return string
     */
    public function getBitting(): string
    {
        return $this->bitting;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }


}