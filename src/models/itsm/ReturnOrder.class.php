<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/10/2019
 * Time: 10:06 PM
 */


namespace models\itsm;


use models\Model;

class ReturnOrder extends Model
{
    private $id;
    private $number;
    private $type;
    private $vendorRMA;
    private $orderDate;
    private $vendor;
    private $status;
    private $notes;
    private $warehouse;
    private $sent;
    private $sendDate;
    private $received;
    private $receiveDate;
    private $canceled;
    private $cancelDate;

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
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getVendorRMA(): string
    {
        return $this->vendorRMA;
    }

    /**
     * @return string|null
     */
    public function getOrderDate(): ?string
    {
        return $this->orderDate;
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
    public function getWarehouse(): int
    {
        return $this->warehouse;
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
     * @return int
     */
    public function getCanceled(): int
    {
        return $this->canceled;
    }

    /**
     * @return string|null
     */
    public function getCancelDate(): ?string
    {
        return $this->cancelDate;
    }

}