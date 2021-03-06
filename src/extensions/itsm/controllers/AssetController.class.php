<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/11/2019
 * Time: 10:15 AM
 */


namespace extensions\itsm\controllers;


use business\AttributeOperator;
use extensions\facilities\business\BuildingOperator;
use extensions\facilities\business\LocationOperator;
use extensions\itsm\business\AssetOperator;
use extensions\itsm\business\CommodityOperator;
use extensions\itsm\business\DiscardOrderOperator;
use extensions\itsm\business\PurchaseOrderOperator;
use extensions\itsm\business\WarehouseOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class AssetController extends Controller
{
    const SEARCH_FIELDS = array('assetTag', 'serialNumber', 'inWarehouse', 'isDiscarded', 'buildingCode',
        'locationCode', 'warehouseCode', 'purchaseOrder', 'manufacturer', 'model', 'commodityCode', 'commodityName',
        'commodityType', 'assetType', 'isVerified', 'notes');

    const EDIT_FIELDS = array('assetTag', 'serialNumber', 'notes', 'manufactureDate');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-assets-r');

        $param = $this->request->next();

        if($param == 'worksheet')
        {
            $worksheet = new AssetWorksheetController($this->request);
            return $worksheet->getResponse();
        }

        if($this->request->method() === HTTPRequest::GET)
        {
            switch($param)
            {
                case null:
                    return $this->getSearchResult();
                default:
                    if($this->request->next() == 'children')
                        return $this->getChildren($param);
                    return $this->getAsset($param);
            }
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            $action = $this->request->next();

            if($param === 'search')
                return $this->getSearchResult(TRUE);
            else if($param !== NULL AND $action == 'parent')
                return $this->linkToParent($param);
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            $action = $this->request->next();

            if($action === NULL)
                return $this->updateAsset($param);

            if($action == 'verify')
                return $this->verifyAsset($param);
            else if($action == 'unverify')
                return $this->unVerifyAsset($param);
            else if($action == 'location')
                return $this->setLocation($param);
            else if($action == 'warehouse')
                return $this->setWarehouse($param);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            $action = $this->request->next();

            if($param !== NULL AND $action == 'parent')
                return $this->unlinkFromParent($param);
        }

        return NULL;
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getAsset(?string $param): HTTPResponse
    {
        $asset = AssetOperator::getAsset((int)$param);
        $commodity = CommodityOperator::getCommodity($asset->getCommodity());

        $hasLocation = $asset->getLocation() !== NULL ? TRUE : FALSE;
        $location = null;
        $building = null;

        if($hasLocation)
        {
            $location = LocationOperator::getLocation($asset->getLocation());
            $building = BuildingOperator::getBuilding($location->getBuilding());
        }

        $hasWarehouse = $asset->getWarehouse() !== NULL ? TRUE : FALSE;
        $warehouse = null;

        if($hasWarehouse)
        {
            $warehouse = WarehouseOperator::getWarehouse($asset->getWarehouse());
        }

        return new HTTPResponse(HTTPResponse::OK, array(
            'assetTag' => $asset->getAssetTag(),
            'inWorksheet' => AssetOperator::isAssetInWorksheet($asset->getId()),
            'commodity' => $asset->getCommodity(),
            'commodityCode' => $commodity->getCode(),
            'commodityName' => $commodity->getName(),
            'commodityType' => AttributeOperator::nameFromId($commodity->getCommodityType()),
            'commodityManufacturer' => $commodity->getManufacturer(),
            'commodityModel' => $commodity->getModel(),
            'assetType' => AttributeOperator::nameFromId($commodity->getAssetType()),
            'warehouse' => $asset->getWarehouse(),
            'warehouseCode' => $hasWarehouse ? $warehouse->getCode() : NULL,
            'warehouseName' => $hasWarehouse ? $warehouse->getName() : NULL,
            'parentAssetTag' => AssetOperator::assetTagFromId($asset->getParent()),
            'location' => $asset->getLocation(),
            'building' => $hasLocation ? $building->getId() : NULL,
            'buildingCode' => $hasLocation ? $building->getCode() : NULL,
            'buildingName' => $hasLocation ? $building->getName() : NULL,
            'locationCode' => $hasLocation ? $location->getCode() : NULL,
            'locationName' => $hasLocation ? $location->getName() : NULL,
            'serialNumber' => $asset->getSerialNumber(),
            'manufactureDate' => $asset->getManufactureDate(),
            'purchaseOrder' => PurchaseOrderOperator::numberFromId($asset->getPurchaseOrder()),
            'notes' => $asset->getNotes(),
            'discarded' => $asset->getDiscarded(),
            'discardDate' => $asset->getDiscardDate(),
            'discardOrder' => DiscardOrderOperator::numberFromId((int)$asset->getDiscardOrder()),
            'verified' => $asset->getVerified(),
            'verifyDate' => $asset->getVerifyDate()
        ));
    }

    /**
     * @param bool $search
     * @param bool $strict
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     */
    private function getSearchResult(bool $search = FALSE, bool $strict = FALSE): HTTPResponse
    {
        if($search)
        {
            $args = $this->getFormattedBody(self::SEARCH_FIELDS, $strict);

            $assets = AssetOperator::search($args['assetTag'], $args['serialNumber'], $args['inWarehouse'],
                $args['isDiscarded'], $args['buildingCode'], $args['locationCode'], $args['warehouseCode'],
                $args['purchaseOrder'], $args['manufacturer'], $args['model'], $args['commodityCode'],
                $args['commodityName'], $args['commodityType'], $args['assetType'], $args['isVerified']);
        }
        else
        {
            $assets = AssetOperator::search();
        }

        $data = array();

        foreach($assets as $asset)
        {
            $commodity = CommodityOperator::getCommodity($asset->getCommodity());

            $data[] = array(
                'inWorksheet' => AssetOperator::isAssetInWorksheet($asset->getId()),
                'assetTag' => $asset->getAssetTag(),
                'commodityCode' => $commodity->getCode(),
                'commodityName' => $commodity->getName(),
                'assetType' => AttributeOperator::nameFromId($commodity->getAssetType()),
                'serialNumber' => $asset->getSerialNumber(),
                'location' => LocationOperator::getFullLocationCode($asset->getLocation()),
                'warehouse' => WarehouseOperator::codeFromId($asset->getWarehouse()),
                'verified' => $asset->getVerified()
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
    private function getChildren(?string $param): HTTPResponse
    {
        $asset = AssetOperator::getAsset((int) $param);

        $children = AssetOperator::getChildren($asset->getAssetTag());

        $data = array();

        foreach($children as $child)
        {
            $data[] = array(
                'assetTag' => $child->getAssetTag(),
                'commodityName' => CommodityOperator::assetTypeNameFromId($child->getCommodity())
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string|null $assetTag
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws EntryNotFoundException
     */
    private function updateAsset(?string $assetTag): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-assets-w'));

        $asset = AssetOperator::getAsset((int)$assetTag);

        $args = $this->getFormattedBody(self::EDIT_FIELDS, TRUE);

        $errors = AssetOperator::updateAsset($asset, $args['assetTag'], $args['serialNumber'], $args['notes'], $args['manufactureDate']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $tag
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function linkToParent(?string $tag): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-assets-w'));

        $asset = AssetOperator::getAsset((int)$tag);

        $args = $this->getFormattedBody(array('parentAssetTag'));

        $errors = AssetOperator::linkToParent($asset, (int)$args['parentAssetTag']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $tag
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function unlinkFromParent(?string $tag): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-assets-w'));

        $asset = AssetOperator::getAsset((int)$tag);

        $errors = AssetOperator::unlinkFromParent($asset);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $tag
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function verifyAsset(?string $tag): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-assets-w'));

        $asset = AssetOperator::getAsset((int)$tag);

        $errors = AssetOperator::verifyAsset($asset);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $tag
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function unVerifyAsset(?string $tag): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-assets-w'));

        $asset = AssetOperator::getAsset((int)$tag);

        $errors = AssetOperator::unVerifyAsset($asset);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function setLocation(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-assets-w');

        $asset = AssetOperator::getAsset((string)$param);

        $args = self::getFormattedBody(array('buildingCode', 'locationCode'));

        $errors = AssetOperator::setLocation($asset, (string)$args['buildingCode'], (string)$args['locationCode']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function setWarehouse(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-assets-w');

        $asset = AssetOperator::getAsset((string) $param);

        $args = self::getFormattedBody(array('warehouseCode'));

        $errors = AssetOperator::setWarehouse($asset, (string)$args['warehouseCode']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}
