<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/31/2019
 * Time: 11:02 AM
 */


namespace extensions\itsm\models;


use extensions\itsm\database\DiscardOrderDatabaseHandler;
use models\Model;

class DiscardOrder extends Model
{
    private $id;
    private $number;
    private $date;
    private $notes;
    private $approved;
    private $approveDate;
    private $fulfilled;
    private $fulfillDate;
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
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
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
    public function getApproved(): int
    {
        return $this->approved;
    }

    /**
     * @return string|null
     */
    public function getApproveDate(): ?string
    {
        return $this->approveDate;
    }

    /**
     * @return int
     */
    public function getFulfilled(): int
    {
        return $this->fulfilled;
    }

    /**
     * @return string|null
     */
    public function getFulfillDate(): ?string
    {
        return $this->fulfillDate;
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

    /**
     * @return Asset[]
     * @throws \exceptions\DatabaseException
     */
    public function getAssets(): array
    {
        return DiscardOrderDatabaseHandler::selectAssetsByOrder($this->id);
    }
}
