<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 5/06/2019
 * Time: 3:21 AM
 */


namespace extensions\itsm\controllers;


use business\AttributeOperator;
use extensions\itsm\business\CommodityOperator;
use extensions\itsm\business\PurchaseOrderOperator;
use extensions\itsm\business\VendorOperator;
use extensions\itsm\business\WarehouseOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class PurchaseOrderController extends Controller
{
    private const FIELDS = array('orderDate', 'warehouse', 'vendor', 'notes');
    private const SEARCH_FIELDS = array('number', 'vendor', 'warehouse', 'orderStart', 'orderEnd', 'status');
    private const COMMODITY_FIELDS = array('commodity', 'quantity', 'unitCost');
    private const COST_FIELDS = array('cost', 'notes');
    private const RECEIVE_FIELDS = array('receiveDate', 'startAssetTag');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     * @throws \exceptions\ValidationError
     * @throws \exceptions\ValidationError
     * @throws \exceptions\ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-purchaseorders-r');

        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            if($param === 'statuses')
                return $this->getStatuses();

            if($param === NULL)
            {
                return $this->search();
            }

            switch($this->request->next())
            {
                case 'commodities':
                    return $this->getCommodities($param);
                case 'costitems':
                    return $this->getCostItems($param);
                default:
                    return $this->getPO($param);
            }
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            if($param === 'search')
                return $this->search(TRUE);
            else if($param === NULL)
                return $this->create();

            switch($this->request->next())
            {
                case 'commodities':
                    return $this->addCommodity($param);
                case 'costitems':
                    return $this->addCostItem($param);
            }
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            $operation = $this->request->next();

            switch($operation)
            {
                case 'send':
                    return $this->send($param);
                case 'cancel':
                    return $this->cancel($param);
                case 'receive':
                    return $this->receive($param);
                default:
                    return $this->update($param);
            }
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            switch($this->request->next())
            {
                case 'commodities':
                    return $this->removeCommodity($param, $this->request->next());
                case 'costitems':
                    return $this->removeCostItem($param, $this->request->next());
            }
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

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function create(): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-purchaseorders-w');
        $args = self::getFormattedBody(self::FIELDS);

        return new HTTPResponse(HTTPResponse::CREATED, PurchaseOrderOperator::create($args));
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function update(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-purchaseorders-w');

        $po = PurchaseOrderOperator::getPO((int)$param);
        $args = self::getFormattedBody(self::FIELDS);
        PurchaseOrderOperator::update($po, $args);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getCommodities(?string $param): HTTPResponse
    {
        $po = PurchaseOrderOperator::getPO((int) $param);

        $data = array();

        foreach($po->getCommodities() as $commodityItem)
        {
            $commodity = CommodityOperator::getCommodity($commodityItem->getCommodity());

            $data[] = array(
                'id' => $commodityItem->getId(),
                'commodity' => $commodityItem->getCommodity(),
                'commodityCode' => $commodity->getCode(),
                'commodityName' => $commodity->getName(),
                'quantity' => $commodityItem->getQuantity(),
                'unitCost' => $commodityItem->getUnitCost()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getCostItems(?string $param): HTTPResponse
    {
        $po = PurchaseOrderOperator::getPO((int) $param);

        $data = array();

        foreach($po->getCostItems() as $cost)
        {
            $data[] = array(
                'id' => $cost->getId(),
                'cost' => $cost->getCost(),
                'notes' => $cost->getNotes()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string $po
     * @param string $commodity
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function removeCommodity(string $po, string $commodity): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-purchaseorders-w');

        $po = PurchaseOrderOperator::getPO((int) $po);

        $errors = PurchaseOrderOperator::removeCommodity($po, (int)$commodity);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string $po
     * @param string $cost
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function removeCostItem(string $po, string $cost): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-purchaseorders-w');

        $po = PurchaseOrderOperator::getPO((int) $po);

        $errors = PurchaseOrderOperator::removeCost($po, (int)$cost);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string $po
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function addCommodity(string $po): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-purchaseorders-w');
        $po = PurchaseOrderOperator::getPO((int) $po);
        $args = self::getFormattedBody(self::COMMODITY_FIELDS);

        return new HTTPResponse(HTTPResponse::CREATED, PurchaseOrderOperator::addCommodity($po, (string)$args['commodity'], (int)$args['quantity'], (float)$args['unitCost']));
    }

    /**
     * @param string $po
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function addCostItem(string $po): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-purchaseorders-w');
        $po = PurchaseOrderOperator::getPO((int) $po);
        $args = self::getFormattedBody(self::COST_FIELDS);

        return new HTTPResponse(HTTPResponse::CREATED, PurchaseOrderOperator::addCost($po, (float)$args['cost'], (string)$args['notes']));
    }

    /**
     * @param string $po
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function send(string $po): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-purchaseorders-w');
        $po = PurchaseOrderOperator::getPO((int) $po);

        PurchaseOrderOperator::send($po);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string $po
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function cancel(string $po): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-purchaseorders-w');
        $po = PurchaseOrderOperator::getPO((int) $po);

        PurchaseOrderOperator::cancel($po);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string $po
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function receive(string $po): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-purchaseorders-w');
        $po = PurchaseOrderOperator::getPO((int)$po);
        $args = self::getFormattedBody(self::RECEIVE_FIELDS);

        if(!ctype_digit($args['startAssetTag']))
            $args['startAssetTag'] = NULL;

        PurchaseOrderOperator::receive($po, (string)$args['receiveDate'], $args['startAssetTag']);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}
