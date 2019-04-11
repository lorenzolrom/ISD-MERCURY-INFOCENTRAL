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
}