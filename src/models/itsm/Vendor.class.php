<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/10/2019
 * Time: 10:04 PM
 */


namespace models\itsm;


class Vendor extends ITSMModel
{
    private $id;
    private $code;
    private $name;
    private $streetAddress;
    private $city;
    private $state;
    private $zipCode;
    private $phone;
    private $fax;
}