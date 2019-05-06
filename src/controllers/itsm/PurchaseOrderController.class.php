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


use controllers\Controller;
use exceptions\EntryInUseException;
use exceptions\EntryNotFoundException;
use models\HTTPResponse;

class PurchaseOrderController extends Controller
{
    private const FIELDS = array('orderDate', 'warehouse', 'vendor', 'notes');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws EntryInUseException
     */
    public function getResponse(): ?HTTPResponse
    {
        return NULL;
    }
}