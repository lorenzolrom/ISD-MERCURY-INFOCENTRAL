<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/10/2019
 * Time: 10:05 PM
 */


namespace models\itsm;


class PurchaseOrder extends ITSMModel
{
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


}