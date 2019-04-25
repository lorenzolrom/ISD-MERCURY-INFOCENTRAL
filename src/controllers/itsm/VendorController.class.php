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
use business\UserOperator;
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
            'fax' => $vendor->getFax(),
            'createDate' => $vendor->getCreateDate(),
            'createUser' => UserOperator::usernameFromId($vendor->getCreateUser()),
            'lastModifyDate' => $vendor->getLastModifyDate(),
            'lastModifyUser' => UserOperator::usernameFromId($vendor->getLastModifyUser())
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
}