<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/25/2019
 * Time: 11:19 AM
 */


namespace controllers\itsm;


use business\itsm\VendorOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class VendorController extends Controller
{
    private const FIELDS = array('code', 'name', 'streetAddress', 'city', 'state', 'zipCode', 'phone', 'fax');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws EntryNotFoundException
     * @throws \exceptions\EntryInUseException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission("itsm_inventory-vendors-r");

        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            switch($param)
            {
                case null:
                    return $this->getSearchResults();
                default:
                    return $this->getVendor($param);
            }
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            switch($param)
            {
                case "search":
                    return $this->getSearchResults(TRUE);
                case null:
                    return $this->createVendor();
            }
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            return $this->updateVendor($param);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            return $this->deleteVendor($param);
        }

        return NULL;
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getVendor(?string $param): HTTPResponse
    {
        $vendor = VendorOperator::getVendor((int) $param);

        return new HTTPResponse(HTTPResponse::OK, array(
            'id' => $vendor->getId(),
            'code' => $vendor->getCode(),
            'name' => $vendor->getName(),
            'streetAddress' => $vendor->getStreetAddress(),
            'city' => $vendor->getCity(),
            'state' => $vendor->getState(),
            'zipCode' => $vendor->getZipCode(),
            'phone' => $vendor->getPhone(),
            'fax' => $vendor->getFax()
        ));
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

            $vendors = VendorOperator::search($args['code'], $args['name'], $args['streetAddress'], $args['city'], $args['state'], $args['zipCode'], $args['phone'], $args['fax']);
        }
        else
        {
            $vendors = VendorOperator::search();
        }

        $data = array();

        foreach($vendors as $vendor)
        {
            $data[] = array(
                'id' => $vendor->getId(),
                'code' => $vendor->getCode(),
                'name' => $vendor->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws EntryNotFoundException
     */
    private function createVendor(): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-vendors-w'));

        $args = $this->getFormattedBody(self::FIELDS,TRUE);

        $errors = VendorOperator::createVendor($args['code'], $args['name'], $args['streetAddress'], $args['city'],
            $args['state'], $args['zipCode'], $args['phone'], $args['fax']);

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
    private function updateVendor(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-vendors-w'));

        $vendor = VendorOperator::getVendor((int) $param);

        $args = $this->getFormattedBody(self::FIELDS,TRUE);

        $errors = VendorOperator::updateVendor($vendor, $args['code'], $args['name'], $args['streetAddress'], $args['city'],
            $args['state'], $args['zipCode'], $args['phone'], $args['fax']);

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
    private function deleteVendor(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_inventory-vendors-w'));

        $vendor = VendorOperator::getVendor((int) $param);
        VendorOperator::deleteVendor($vendor);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}