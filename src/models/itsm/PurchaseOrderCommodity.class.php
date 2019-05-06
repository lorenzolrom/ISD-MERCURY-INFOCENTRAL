<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/06/2019
 * Time: 4:11 AM
 */


namespace models\itsm;


use models\Model;

class PurchaseOrderCommodity extends Model
{
    private $id;
    private $purchaseOrder;
    private $commodity;
    private $quantity;
    private $unitCost;

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
    public function getPurchaseOrder(): int
    {
        return $this->purchaseOrder;
    }

    /**
     * @return int
     */
    public function getCommodity(): int
    {
        return $this->commodity;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return float
     */
    public function getUnitCost(): float
    {
        return $this->unitCost;
    }


}