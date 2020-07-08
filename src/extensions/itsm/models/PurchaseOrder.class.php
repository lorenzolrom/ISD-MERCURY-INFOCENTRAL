<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 4/10/2019
 * Time: 10:05 PM
 */


namespace extensions\itsm\models;


use extensions\itsm\business\VendorOperator;
use extensions\itsm\business\WarehouseOperator;
use extensions\itsm\database\PurchaseOrderDatabaseHandler;
use exceptions\ValidationException;
use models\Model;
use utilities\Validator;

class PurchaseOrder extends Model
{
    private const ORDER_DATE = array(
        'name' => 'Order Date',
        'type' => 'date'
    );

    private $id;
    private $number;
    private $orderDate;
    private $warehouse;
    private $vendor;
    private $status;
    private $notes;
    private $sent;
    private $sendDate;
    private $received;
    private $receiveDate;
    private $cancelDate;
    private $canceled;

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
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getOrderDate(): string
    {
        return $this->orderDate;
    }

    /**
     * @return int
     */
    public function getWarehouse(): int
    {
        return $this->warehouse;
    }

    /**
     * @return int
     */
    public function getVendor(): int
    {
        return $this->vendor;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getNotes(): string
    {
        return $this->notes;
    }

    /**
     * @return int
     */
    public function getSent(): int
    {
        return $this->sent;
    }

    /**
     * @return string|null
     */
    public function getSendDate(): ?string
    {
        return $this->sendDate;
    }

    /**
     * @return int
     */
    public function getReceived(): int
    {
        return $this->received;
    }

    /**
     * @return string|null
     */
    public function getReceiveDate(): ?string
    {
        return $this->receiveDate;
    }

    /**
     * @return string|null
     */
    public function getCancelDate(): ?string
    {
        return $this->cancelDate;
    }

    /**
     * @return int
     */
    public function getCanceled(): int
    {
        return $this->canceled;
    }

    /**
     * @return PurchaseOrderCommodity[]
     * @throws \exceptions\DatabaseException
     */
    public function getCommodities(): array
    {
        return PurchaseOrderDatabaseHandler::selectPOCommodities($this->id);
    }

    /**
     * @return PurchaseOrderCostItem[]
     * @throws \exceptions\DatabaseException
     */
    public function getCostItems(): array
    {
        return PurchaseOrderDatabaseHandler::selectPOCostItems($this->id);
    }

    /**
     * @param string|null $orderDate
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateOrderDate(?string $orderDate): bool
    {
        return Validator::validate(self::ORDER_DATE, $orderDate);
    }

    /**
     * @param string|null $warehouse
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateWarehouse(?string $warehouse): bool
    {
        if(!WarehouseOperator::codeInUse((string) $warehouse))
            throw new ValidationException('Warehouse not found', ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }

    /**
     * @param string|null $vendor
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateVendor(?string $vendor): bool
    {
        if(!VendorOperator::codeInUse((string) $vendor))
            throw new ValidationException('Vendor not found', ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }
}
