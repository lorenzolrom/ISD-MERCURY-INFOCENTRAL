<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/12/2019
 * Time: 7:06 PM
 */


namespace controllers\itsm;


use business\AttributeOperator;
use business\facilities\LocationOperator;
use business\itsm\AssetOperator;
use business\itsm\CommodityOperator;
use business\itsm\WarehouseOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use database\itsm\AssetWorksheetDatabaseHandler;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class AssetWorksheetController extends Controller
{

    /**
     * @return HTTPResponse|null
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        if($this->request->method() === HTTPRequest::GET)
        {
            if($this->request->next() === 'count')
                return $this->getWorksheetCount();
            return $this->getWorksheet();
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            $action = $this->request->next();

            if($action == 'location')
                return $this->setLocation();
            else if($action == 'warehouse')
                return $this->setWarehouse();
            else if($action == 'verify')
                return $this->verify();
            else if($action == 'unverify')
                return $this->unVerify();
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            return $this->addToWorksheet();
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            $action = $this->request->next();

            if($action !== NULL)
                return $this->removeFromWorksheet($action);
            else
                return $this->clearWorksheet();
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getWorksheet(): HTTPResponse
    {
        $data = array();

        foreach(AssetOperator::getWorksheet() as $asset)
        {
            $commodity = CommodityOperator::getCommodity($asset->getCommodity());

            $data[] = array(
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
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getWorksheetCount(): HTTPResponse
    {
        return new HTTPResponse(HTTPResponse::OK, array('count' => AssetOperator::getWorksheetCount()));
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function clearWorksheet(): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-assets-w'));

        return new HTTPResponse(HTTPResponse::OK, array('removed' => AssetWorksheetDatabaseHandler::clearWorksheet()));
    }

    /**
     * @param string|null $tag
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function removeFromWorksheet(?string $tag): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-assets-w'));

        $asset = AssetOperator::getAsset((int)$tag);

        $errors = AssetOperator::removeFromWorksheet($asset);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function addToWorksheet(): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-assets-w'));

        $vars = self::getFormattedBody(array('assets'));

        $errors = AssetOperator::addToWorksheet($vars['assets']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::CREATED, $errors);
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function verify(): HTTPResponse
    {
        foreach(AssetOperator::getWorksheet() as $asset)
        {
            AssetOperator::verifyAsset($asset);
        }

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function unVerify(): HTTPResponse
    {
        foreach(AssetOperator::getWorksheet() as $asset)
        {
            AssetOperator::unVerifyAsset($asset);
        }

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function setLocation(): HTTPResponse
    {
        $args = self::getFormattedBody(array('buildingCode', 'locationCode'));

        try
        {
            LocationOperator::getLocationByCode((string)$args['buildingCode'], (string)$args['locationCode']);
        }
        catch(EntryNotFoundException $e)
        {
            return new HTTPResponse(HTTPResponse::CONFLICT, array('errors' => array('Location not found')));
        }

        foreach(AssetOperator::getWorksheet() as $asset)
        {
            AssetOperator::setLocation($asset, $args['buildingCode'], $args['locationCode']);
        }

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function setWarehouse(): HTTPResponse
    {
        $args = self::getFormattedBody(array('warehouseCode'));

        if(WarehouseOperator::idFromCode((string)$args['warehouseCode']) === NULL)
            return new HTTPResponse(HTTPResponse::CONFLICT, array('errors' => array('Warehouse not found')));

        foreach(AssetOperator::getWorksheet() as $asset)
        {
            AssetOperator::setWarehouse($asset, $args['warehouseCode']);
        }

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}