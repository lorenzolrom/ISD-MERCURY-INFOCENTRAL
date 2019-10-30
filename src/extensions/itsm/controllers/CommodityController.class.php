<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/12/2019
 * Time: 3:22 PM
 */


namespace extensions\itsm\controllers;


use business\AttributeOperator;
use extensions\itsm\business\CommodityOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class CommodityController extends Controller
{
    private const SEARCH_FIELDS = array('code', 'name', 'commodityType', 'assetType', 'manufacturer', 'model', 'unitCost');
    private const ASSET_TYPE_FIELDS = array('code', 'name');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws EntryNotFoundException
     * @throws \exceptions\EntryInUseException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-purchaseorders-w', 'itsm_inventory-commodities-r', 'itsm_inventory-assets-r'));

        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            switch($param)
            {
                case "commodityTypes":
                    return $this->getCommodityTypes();
                case "assetTypes":
                    $param = $this->request->next();
                    switch($param)
                    {
                        case null:
                            return $this->getAssetTypes();
                        default:
                            return $this->getAssetType($param);
                    }
                case null:
                    return $this->getSearchResult();
                default:
                    return $this->getById($param);
            }
        }

        if($this->request->method() === HTTPRequest::PUT)
        {
            CurrentUserController::validatePermission('itsm_inventory-commodities-w');
            switch($param)
            {
                case "assetTypes":
                    return $this->updateAssetType($this->request->next());
                default:
                    return $this->updateCommodity($param);
            }
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            switch($param)
            {
                case "search":
                    return $this->getSearchResult(TRUE);
                case "assetTypes":
                    CurrentUserController::validatePermission('itsm_inventory-commodities-w');
                    return $this->createAssetType();
                case null:
                    CurrentUserController::validatePermission('itsm_inventory-commodities-w');
                    return $this->createCommodity();
            }
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            CurrentUserController::validatePermission('itsm_inventory-commodities-w');
            switch($param)
            {
                case "assetTypes":
                    return $this->deleteAssetType($this->request->next());
                default:
                    return $this->deleteCommodity($param);
            }
        }

        return NULL;
    }

    /**
     * @param string $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getById(string $param): HTTPResponse
    {
        $commodity = CommodityOperator::getCommodity((int) $param);

        $data = array(
            'id' => $commodity->getId(),
            'code' => $commodity->getCode(),
            'name' => $commodity->getName(),
            'commodityType' => AttributeOperator::codeFromId($commodity->getCommodityType()),
            'commodityTypeName' => AttributeOperator::nameFromId($commodity->getCommodityType()),
            'assetType' => AttributeOperator::codeFromId($commodity->getAssetType()),
            'assetTypeName' => AttributeOperator::nameFromId($commodity->getAssetType()),
            'manufacturer' => $commodity->getManufacturer(),
            'model' => $commodity->getModel(),
            'unitCost' => $commodity->getUnitCost()
        );

        return new HTTPResponse(HTTPResponse::OK, $data);
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
            $fields = $this->getFormattedBody(self::SEARCH_FIELDS, $strict);

            $commodities = CommodityOperator::search($fields['code'], $fields['name'], $fields['commodityType'], $fields['assetType'], $fields['manufacturer'], $fields['model']);
        }
        else
            $commodities = CommodityOperator::search();

        $results = array();

        foreach($commodities as $commodity)
        {
            $results[] = array(
                'id' => $commodity->getId(),
                'code' => $commodity->getCode(),
                'name' => $commodity->getName(),
                'commodityType' => AttributeOperator::nameFromId($commodity->getCommodityType()),
                'assetType' => AttributeOperator::nameFromId($commodity->getAssetType()),
                'manufacturer' => $commodity->getManufacturer(),
                'model' => $commodity->getModel()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $results);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getCommodityTypes(): HTTPResponse
    {
        $types = CommodityOperator::getCommodityTypes();

        $data = array();

        foreach($types as $type)
        {
            $data[] = array(
                'id' => $type->getId(),
                'code' => $type->getCode(),
                'name' => $type->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getAssetTypes(): HTTPResponse
    {
        $types = CommodityOperator::getAssetTypes();

        $data = array();

        foreach($types as $type)
        {
            $data[] = array(
                'id' => $type->getId(),
                'code' => $type->getCode(),
                'name' => $type->getName()
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
    private function getAssetType(?string $param): HTTPResponse
    {
        $type = AttributeOperator::getAttributeByExtensionTypeId('itsm', 'asty', (int)$param);

        return new HTTPResponse(HTTPResponse::OK, array(
            'id '=> $type->getId(),
            'code' => $type->getCode(),
            'name' => $type->getName()
        ));
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws EntryNotFoundException
     */
    private function createAssetType(): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-commodities-w'));

        $args = $this->getFormattedBody(self::ASSET_TYPE_FIELDS, TRUE);

        $errors = AttributeOperator::createAttribute('itsm', 'asty', $args['code'], $args['name']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::CREATED, $errors);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function updateAssetType(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-commodities-w'));

        $attribute = AttributeOperator::getAttribute((int) $param);

        $args = $this->getFormattedBody(self::SEARCH_FIELDS, TRUE);

        $errors = AttributeOperator::updateAttribute($attribute, $args['code'], $args['name']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryInUseException
     * @throws \exceptions\SecurityException
     */
    private function deleteAssetType(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-commodities-w'));

        $attribute = AttributeOperator::getAttribute((int) $param);

        AttributeOperator::deleteAttribute($attribute);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws EntryNotFoundException
     */
    private function createCommodity(): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-commodities-w'));

        $args = $this->getFormattedBody(self::SEARCH_FIELDS, TRUE);

        $errors = CommodityOperator::createCommodity($args['code'], $args['name'], $args['commodityType'], $args['assetType'], $args['manufacturer'], $args['model'], (float)$args['unitCost']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::CREATED, $errors);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws EntryNotFoundException
     */
    private function updateCommodity(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-commodities-w'));
        $commodity = CommodityOperator::getCommodity((int)$param);

        $args = $this->getFormattedBody(self::SEARCH_FIELDS, TRUE);

        $errors = CommodityOperator::updateCommodity($commodity, $args['code'], $args['name'], $args['commodityType'], $args['assetType'], $args['manufacturer'], $args['model'], (float)$args['unitCost']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryInUseException
     * @throws \exceptions\SecurityException
     */
    private function deleteCommodity(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-commodities-w'));

        CommodityOperator::deleteCommodity(CommodityOperator::getCommodity((int)$param));

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}