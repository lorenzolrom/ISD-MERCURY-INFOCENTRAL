<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/06/2019
 * Time: 4:14 AM
 */


namespace models\itsm;


use models\Model;
use utilities\Validator;

class PurchaseOrderCostItem extends Model
{
    private const COST = array(
        'name' => 'Cost',
        'type' => 'float',
        'positive' => TRUE
    );

    private $id;
    private $purchaseOrder;
    private $cost;
    private $notes;

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
     * @return float
     */
    public function getCost(): float
    {
        return $this->cost;
    }

    /**
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param string|null $cost
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateCost(?string $cost): bool
    {
        return Validator::validate(self::COST, $cost);
    }
}