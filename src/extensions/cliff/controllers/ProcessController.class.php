<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/11/2020
 * Time: 10:08 PM
 */


namespace extensions\cliff\controllers;


use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use extensions\cliff\business\CoreOperator;
use extensions\cliff\business\KeyOperator;
use models\HTTPRequest;
use models\HTTPResponse;

class ProcessController extends Controller
{
    private const BUILD_FIELDS = array('systemCode', 'controlStamp', 'operatingStamps'); // Params for building core for display only
    private const BUILD_TO_CORE_FIELDS = array('systemCode', 'controlStamp', 'operatingStamps', 'buildToCore'); // Params for building core to Core object
    private const SEQ_KEYS_FIELDS = array('systemCode', 'stamp', 'type', 'keyway', 'c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'seqStart', 'seqEnd', 'padding');

    /**
     * @return HTTPResponse|null
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        $p1 = $this->request->next();
        $p2 = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            if($p1 === 'read' AND $p2 !== NULL)
                return $this->readCore((int)$p2);
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            if($p1 === 'build' AND $p2 === NULL) // Build a core just for display purposes
                return $this->buildCore();
            else if($p1 === 'compare' AND $p2 === NULL)
                return $this->compareCores();
            else if($p1 === 'sequence' AND $p2 === NULL)
                return $this->sequenceKeys();
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            if($p1 === 'build' AND $p2 === NULL) // Build a core and modify an existing core
                return $this->buildCoreToCore();
        }

        return NULL;
    }

    /**
     * @param int $id
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function readCore(int $id): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-r');

        $core = CoreOperator::get($id);

        return new HTTPResponse(HTTPResponse::OK, CoreOperator::readCore($core));
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function buildCore(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-r');
        $body = self::getFormattedBody(self::BUILD_FIELDS);

        return new HTTPResponse(HTTPResponse::OK, CoreOperator::buildCore((string)$body['systemCode'], (string)$body['controlStamp'], (string)$body['operatingStamps']));
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function buildCoreToCore(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-w');
        $body = self::getFormattedBody(self::BUILD_TO_CORE_FIELDS);

        return new HTTPResponse(HTTPResponse::CREATED, CoreOperator::buildCore((string)$body['systemCode'], (string)$body['controlStamp'], (string)$body['operatingStamps'], (string)$body['buildToCore']));
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function compareCores(): HTTPResponse {
        CurrentUserController::validatePermission('cliff-r');
        $body = self::getFormattedBody(array('systemCode, coreStamps'));

        return new HTTPResponse(HTTPResponse::OK, CoreOperator::compareCores((string)$body['systemCode'], (string)$body['coreStamps']));
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function sequenceKeys(): HTTPResponse
    {
        CurrentUserController::validatePermission('cliff-w');
        $body = self::getFormattedBody(self::SEQ_KEYS_FIELDS);
        $numKeys = KeyOperator::sequenceKeys($body);

        return new HTTPResponse(HTTPResponse::CREATED, array('count' => $numKeys));
    }
}
