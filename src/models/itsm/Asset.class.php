<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/10/2019
 * Time: 10:08 PM
 */


namespace models\itsm;


class Asset extends ITSMModel
{
    private $id;
    private $commodity;
    private $warehouse;
    private $assetTag;
    private $parent;
    private $location;
    private $serialNumber;
    private $manufactureDate;
    private $purchaseOrder;
    private $notes;
    private $discarded;
    private $discardDate;
    private $verified;
    private $verifyDate;
    private $verifyUser;

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
    public function getCommodity(): int
    {
        return $this->commodity;
    }

    /**
     * @return int|null
     */
    public function getWarehouse(): ?int
    {
        return $this->warehouse;
    }

    /**
     * @return int
     */
    public function getAssetTag(): int
    {
        return $this->assetTag;
    }

    /**
     * @return int|null
     */
    public function getParent(): ?int
    {
        return $this->parent;
    }

    /**
     * @return int|null
     */
    public function getLocation(): ?int
    {
        return $this->location;
    }

    /**
     * @return string
     */
    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    /**
     * @return string
     */
    public function getManufactureDate(): string
    {
        return $this->manufactureDate;
    }

    /**
     * @return int
     */
    public function getPurchaseOrder(): int
    {
        return $this->purchaseOrder;
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
    public function getDiscarded(): int
    {
        return $this->discarded;
    }

    /**
     * @return string|null
     */
    public function getDiscardDate(): ?string
    {
        return $this->discardDate;
    }

    /**
     * @return int
     */
    public function getVerified(): int
    {
        return $this->verified;
    }

    /**
     * @return string|null
     */
    public function getVerifyDate(): ?string
    {
        return $this->verifyDate;
    }

    /**
     * @return int|null
     */
    public function getVerifyUser(): ?int
    {
        return $this->verifyUser;
    }


}