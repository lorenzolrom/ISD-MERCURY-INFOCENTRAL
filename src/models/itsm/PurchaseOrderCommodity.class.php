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


use business\itsm\CommodityOperator;
use exceptions\ValidationException;
use models\Model;
use utilities\Validator;

class PurchaseOrderCommodity extends Model
{
    private const QUANTITY = array(
        'name' => 'Quantity',
        'type' => 'int',
        'positive' => TRUE
    );

    private const UNIT_COST = array(
        'name' => 'Unit cost',
        'type' => 'float',
        'positive' => TRUE
    );

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

    /**
     * @param string|null $commodity
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateCommodity(?string $commodity): bool
    {
        if(!CommodityOperator::codeInUse((string)$commodity))
            throw new ValidationException('Commodity not found', ValidationException::VALUE_ALREADY_TAKEN);

        return TRUE;
    }

    /**
     * @param string|null $quantity
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateQuantity(?string $quantity): bool
    {
        return Validator::validate(self::QUANTITY, $quantity);
    }

    /**
     * @param string|null $unitCost
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateUnitCost(?string $unitCost): bool
    {
        return Validator::validate(self::UNIT_COST, $unitCost);
    }
}