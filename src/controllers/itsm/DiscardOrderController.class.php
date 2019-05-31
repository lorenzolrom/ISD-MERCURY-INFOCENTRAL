<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/31/2019
 * Time: 12:01 PM
 */


namespace controllers\itsm;


use business\itsm\DiscardOrderOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use models\HTTPRequest;
use models\HTTPResponse;

class DiscardOrderController extends Controller
{
    private const SEARCH_FIELDS = array('number', 'startDate', 'endDate', 'approved', 'fulfilled', 'canceled');
    private const ASSET_FIELDS = array('assetTag');
    private const FIELDS = array('notes');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws ValidationError
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-discards-r');

        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            if($param === NULL)
                return $this->search();
            else if($this->request->next() == 'assets')
                return $this->getAssets($param);
            else
                return $this->get($param);
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            $action = $this->request->next();

            if($action == 'approve')
                return $this->approve($param);
            else if($action == 'fulfill')
                return $this->fulfill($param);
            else if($action == 'cancel')
                return $this->cancel($param);

            return $this->update($param);
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            $action = $this->request->next();

            if($param == 'search')
                return $this->search(TRUE);
            if($action == 'assets')
                return $this->addAsset($param);

            return $this->create();
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            if($this->request->next() == 'assets')
                return $this->removeAsset($param, $this->request->next());
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function create(): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-discards-w');

        $args = self::getFormattedBody(self::FIELDS);

        if($args['notes'] === NULL)
            $args['notes'] = '';

        return new HTTPResponse(HTTPResponse::CREATED, array('id' => DiscardOrderOperator::create($args['notes'])));
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function update(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-discards-w');
        $do = DiscardOrderOperator::getByNumber((int)$param);

        $args = self::getFormattedBody(self::FIELDS);

        if($args['notes'] === NULL)
            $args['notes'] = '';

        DiscardOrderOperator::update($do, $args['notes']);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function approve(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-discards-w');
        $do = DiscardOrderOperator::getByNumber((int)$param);

        DiscardOrderOperator::approve($do);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function fulfill(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-discards-w');
        $do = DiscardOrderOperator::getByNumber((int)$param);

        DiscardOrderOperator::fulfill($do);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function cancel(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-discards-w');
        $do = DiscardOrderOperator::getByNumber((int)$param);

        DiscardOrderOperator::cancel($do);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function addAsset(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-discards-w');
        $do = DiscardOrderOperator::getByNumber((int)$param);

        $args = self::getFormattedBody(self::ASSET_FIELDS);

        DiscardOrderOperator::addAsset($do, $args['assetTag']);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @param string|null $asset
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function removeAsset(?string $param, ?string $asset): HTTPResponse
    {
        CurrentUserController::validatePermission('itsm_inventory-discards-w');
        $do = DiscardOrderOperator::getByNumber((int)$param);

        DiscardOrderOperator::removeAsset($do, $asset);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function get(?string $param): HTTPResponse
    {
        $do = DiscardOrderOperator::getByNumber((int)$param);

        return new HTTPResponse(HTTPResponse::OK, array(
            'number' => $do->getNumber(),
            'notes' => $do->getNotes(),
            'date' => $do->getDate(),
            'approved' => $do->getApproved(),
            'approveDate' => $do->getApproveDate(),
            'fulfilled' => $do->getFulfilled(),
            'fulfillDate' => $do->getFulfillDate(),
            'canceled' => $do->getCanceled(),
            'cancelDate' => $do->getCancelDate()
        ));
    }

    /**
     * @param bool $search
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function search(bool $search = FALSE): HTTPResponse
    {
        if($search == TRUE)
        {
            $args = self::getFormattedBody(self::SEARCH_FIELDS, FALSE);

            if(!is_array($args['approved']))
                $args['approved'] = NULL;
            if(!is_array($args['fulfilled']))
                $args['fulfilled'] = NULL;
            if(!is_array($args['canceled']))
                $args['canceled'] = NULL;

            $args['startDate'] = trim($args['startDate'], '%');
            $args['endDate'] = trim($args['endDate'], '%');

            $dos = DiscardOrderOperator::search($args['number'], $args['startDate'], $args['endDate'], $args['approved'], $args['fulfilled'], $args['canceled']);
        }
        else
            $dos = DiscardOrderOperator::search();

        $data = array();

        foreach($dos as $do)
        {
            $data[] = array(
                'number' => $do->getNumber(),
                'date' => $do->getDate(),
                'approved' => $do->getApproved(),
                'fulfilled' => $do->getFulfilled(),
                'canceled' => $do->getCanceled()
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
    private function getAssets(?string $param): HTTPResponse
    {
        $do = DiscardOrderOperator::getByNumber((int)$param);

        $data = array();

        foreach($do->getAssets() as $asset)
        {
            $data[] = array(
                'assetTag' => $asset->getAssetTag(),
                'serialNumber' => $asset->getSerialNumber()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }
}