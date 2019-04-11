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


class ReturnOrder extends ITSMModel
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
}