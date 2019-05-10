<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/06/2019
 * Time: 3:21 AM
 */


namespace controllers\itsm;


use business\AttributeOperator;
use business\itsm\PurchaseOrderOperator;
use business\itsm\VendorOperator;
use business\itsm\WarehouseOperator;
use controllers\Controller;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class PurchaseOrderController extends Controller
{
    private const FIELDS = array('orderDate', 'warehouse', 'vendor', 'notes');
    private const SEARCH_FIELDS = array('number', 'vendor', 'warehouse', 'orderStart', 'orderEnd', 'status');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     */
    public function getResponse(): ?HTTPResponse
    {
        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            if($param === 'statuses')
                return $this->getStatuses();
            if($param === NULL)
                return $this->search();

            return $this->getPO($param);
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            if($param === 'search')
                return $this->search(TRUE);
        }

        return NULL;
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getPO(?string $param): HTTPResponse
    {
        $po = PurchaseOrderOperator::getPO((int) $param);

        $vendor = VendorOperator::getVendor($po->getVendor());
        $warehouse = WarehouseOperator::getWarehouse($po->getWarehouse());

        return new HTTPResponse(HTTPResponse::OK, array(
            'number' => $po->getNumber(),
            'orderDate' => $po->getOrderDate(),
            'warehouse' => $po->getWarehouse(),
            'warehouseCode' => $warehouse->getCode(),
            'warehouseName' => $warehouse->getName(),
            'vendor' => $po->getVendor(),
            'vendorCode' => $vendor->getCode(),
            'vendorName' => $vendor->getName(),
            'status' => AttributeOperator::nameFromId($po->getStatus()),
            'notes' => $po->getNotes(),
            'sent' => $po->getSent(),
            'sendDate' => $po->getSendDate(),
            'received' => $po->getReceived(),
            'receiveDate' => $po->getReceiveDate(),
            'canceled' => $po->getCanceled(),
            'cancelDate' => $po->getCancelDate()
        ));
    }

    /**
     * @param bool $search
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function search(bool $search = TRUE): HTTPResponse
    {
        if($search)
        {
            $args = self::getFormattedBody(self::SEARCH_FIELDS, FALSE);

            if(!is_array($args['status']))
                $args['status'] = array();

            $pos = PurchaseOrderOperator::search($args['number'], $args['vendor'], $args['warehouse'], $args['orderStart'], $args['orderEnd'], $args['status']);
        }
        else
        {
            $pos = PurchaseOrderOperator::search();
        }

        $data = array();

        foreach($pos as $po)
        {
            $warehouse = WarehouseOperator::getWarehouse($po->getWarehouse());
            $vendor = VendorOperator::getVendor($po->getVendor());

            $data[] = array(
                'number' => $po->getNumber(),
                'orderDate' => $po->getOrderDate(),
                'warehouseCode' => $warehouse->getCode(),
                'warehouseName' => $warehouse->getName(),
                'vendorCode' => $vendor->getCode(),
                'vendorName' => $vendor->getName(),
                'status' => AttributeOperator::nameFromId($po->getStatus())
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getStatuses(): HTTPResponse
    {
        $data = array();

        foreach(PurchaseOrderOperator::getStatuses() as $status)
        {
            $data[] = array(
                'code' => $status->getCode(),
                'name' => $status->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }
}