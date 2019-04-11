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
}