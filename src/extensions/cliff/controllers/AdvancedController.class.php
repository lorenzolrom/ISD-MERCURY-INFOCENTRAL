<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/13/2020
 * Time: 11:49 AM
 */


namespace extensions\cliff\controllers;


use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryInUseException;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use extensions\cliff\business\AdvancedOperator;
use models\HTTPRequest;
use models\HTTPResponse;

/**
 * Handle requests for advanced operations
 *
 * Class AdvancedController
 * @package extensions\cliff\controllers
 */
class AdvancedController extends Controller
{
    private const XREFPEOPLE_FIELDS = array('issuedTo');
    private const XREFLOCATIONS_FIELDS = array('building', 'location');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        $p1 = $this->request->next();
        $p2 = $this->request->next();

        if($this->request->method() === HTTPRequest::POST)
        {
            if($p2 === NULL)
            {
                if($p1 === 'xrefpeople')
                    return $this->xRefPeople();
                else if($p1 === 'xreflocations')
                    return $this->xRefLocations();
            }
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function xRefPeople(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-r');
        return new HTTPResponse(HTTPResponse::OK, AdvancedOperator::xRefPeople(self::getFormattedBody(self::XREFPEOPLE_FIELDS, TRUE)));
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function xRefLocations(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-r');
        return new HTTPResponse(HTTPResponse::OK, AdvancedOperator::xRefLocations(self::getFormattedBody(self::XREFLOCATIONS_FIELDS, TRUE)));
    }
}
