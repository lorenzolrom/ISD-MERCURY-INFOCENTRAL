<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/11/2019
 * Time: 10:15 AM
 */


namespace controllers\itsm;


use business\itsm\AssetOperator;
use business\UserOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class AssetController extends Controller
{

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

        if($this->request->method() == HTTPRequest::GET)
        {
            switch($param)
            {
                default:
                    return $this->getAsset($param);
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
    private function getAsset(?string $param): HTTPResponse
    {
        $asset = AssetOperator::getAsset((int)$param);

        return new HTTPResponse(HTTPResponse::OK, array(
            'assetTag' => $asset->getAssetTag(),
            'commodity' => $asset->getCommodity(),
            'warehouse' => $asset->getWarehouse(),
            'parent' => $asset->getParent(), // TODO: convert to asset tag
            'location' => $asset->getLocation(),
            'serialNumber' => $asset->getSerialNumber(),
            'manufactureDate' => $asset->getManufactureDate(),
            'purchaseOrder' => $asset->getPurchaseOrder(), // TODO: convert to #,
            'notes' => $asset->getNotes(),
            'createDate' => $asset->getCreateDate(),
            'discarded' => $asset->getDiscarded(),
            'discardDate' => $asset->getDiscardDate(),
            'lastModifyDate' => $asset->getLastModifyDate(),
            'lastModifyUser' => UserOperator::usernameFromId($asset->getLastModifyUser()),
            'verified' => $asset->getVerified(),
            'verifyDate' => $asset->getVerifyDate(),
            'verifyUser' => ($asset->getVerifyUser() !== NULL) ? UserOperator::usernameFromId($asset->getVerifyUser()) : NULL
        ));
    }
}