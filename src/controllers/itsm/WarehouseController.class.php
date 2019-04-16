<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/13/2019
 * Time: 7:25 PM
 */


namespace controllers\itsm;


use business\itsm\WarehouseOperator;
use business\UserOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class WarehouseController extends Controller
{
    private const FIELDS = array('code', 'name', 'closed');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\EntryInUseException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission("itsm_inventory-warehouses-r");

        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            switch($param)
            {
                case null:
                    return $this->getSearchResults();
                default:
                    return $this->getWarehouse($param);
            }
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            switch($param)
            {
                case "search":
                    return $this->getSearchResults(TRUE);
                case null:
                    return $this->createWarehouse();
            }
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            return $this->updateWarehouse($param);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            return $this->deleteWarehouse($param);
        }

        return NULL;
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getWarehouse(?string $param): HTTPResponse
    {
        $warehouse = WarehouseOperator::getWarehouse((int) $param);

        return new HTTPResponse(HTTPResponse::OK, array(
            'id' => $warehouse->getId(),
            'code' => $warehouse->getCode(),
            'name' => $warehouse->getName(),
            'closed' => $warehouse->getClosed(),
            'createUser' => UserOperator::usernameFromId($warehouse->getCreateUser()),
            'createDate' => $warehouse->getCreateDate(),
            'lastModifyUser' => UserOperator::usernameFromId($warehouse->getLastModifyUser()),
            'lastModifyDate' => $warehouse->getLastModifyDate()
        ));
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws EntryNotFoundException
     */
    private function createWarehouse(): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-warehouses-w'));

        $args = $this->getFormattedBody(self::FIELDS, TRUE);

        $errors = WarehouseOperator::createWarehouse($args['code'], $args['name']);

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
    private function updateWarehouse(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-warehouses-w'));

        $warehouse = WarehouseOperator::getWarehouse((int)$param);

        $args = $this->getFormattedBody(self::FIELDS, TRUE);

        $errors = WarehouseOperator::updateWarehouse($warehouse, $args['code'], $args['name']);

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
     */
    private function deleteWarehouse(?string $param): HTTPResponse
    {
        $warehouse = WarehouseOperator::getWarehouse((int)$param);
        WarehouseOperator::deleteWarehouse($warehouse);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param bool $search
     * @param bool $strict
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getSearchResults(bool $search = FALSE, bool $strict = FALSE): HTTPResponse
    {
        if($search)
        {
            $args = $this->getFormattedBody(self::FIELDS, $strict);

            $warehouses = WarehouseOperator::search($args['code'], $args['name'], $args['closed']);
        }
        else
        {
            $warehouses = WarehouseOperator::search();
        }

        $data = array();

        foreach($warehouses as $warehouse)
        {
            $data[] = array(
                'id' => $warehouse->getId(),
                'code' => $warehouse->getCode(),
                'name' => $warehouse->getName(),
                'closed' => $warehouse->getClosed()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }
}