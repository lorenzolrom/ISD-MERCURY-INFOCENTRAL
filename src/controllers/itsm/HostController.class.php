<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/27/2019
 * Time: 8:05 PM
 */


namespace controllers\itsm;


use business\itsm\AssetOperator;
use business\itsm\HostOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryInUseException;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class HostController extends Controller
{
    private const FIELDS = array('assetTag', 'ipAddress', 'macAddress', 'systemName', 'systemCPU', 'systemRAM', 'systemOS', 'systemDomain');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws EntryInUseException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_devices-hosts-r', 'itsm_ait-apps-w'));

        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            switch($param)
            {
                case null:
                    return $this->getSearchResults();
                default:
                    return $this->getHost($param);
            }
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            switch($param)
            {
                case "search":
                    return $this->getSearchResults(TRUE);
                case null:
                    return $this->createHost();
            }
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            return $this->updateHost($param);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            return $this->deleteHost($param);
        }

        return NULL;
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getHost(?string $param): HTTPResponse
    {
        $host = HostOperator::getHost((int) $param);

        return new HTTPResponse(HTTPResponse::OK, array(
            'id' => $host->getId(),
            'ipAddress' => $host->getIpAddress(),
            'macAddress' => $host->getMacAddress(),
            'assetTag' => AssetOperator::assetTagFromId($host->getAsset()),
            'systemName' => $host->getSystemName(),
            'systemCPU' => $host->getSystemCPU(),
            'systemRAM' => $host->getSystemRAM(),
            'systemOS' => $host->getSystemOS(),
            'systemDomain' => $host->getSystemDomain()
        ));
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws EntryNotFoundException
     */
    private function createHost(): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_devices-hosts-w'));

        $args = $this->getFormattedBody(self::FIELDS, TRUE);

        $errors = HostOperator::createHost($args['assetTag'], $args['ipAddress'], $args['macAddress'],
            $args['systemName'], $args['systemCPU'], $args['systemRAM'], $args['systemOS'], $args['systemDomain']);

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
    private function updateHost(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_devices-hosts-w'));

        $host = HostOperator::getHost((int) $param);

        $args = $this->getFormattedBody(self::FIELDS, TRUE);

        $errors = HostOperator::updateHost($host, $args['assetTag'], $args['ipAddress'], $args['macAddress'],
            $args['systemName'], $args['systemCPU'], $args['systemRAM'], $args['systemOS'], $args['systemDomain']);

        if(isset($errors['errors']))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryInUseException
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function deleteHost(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission(array('itsm_devices-hosts-w'));

        $host = HostOperator::getHost((int) $param);
        HostOperator::deleteHost($host);

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

            $hosts = HostOperator::search($args['assetTag'], $args['ipAddress'], $args['macAddress'], $args['systemName'], $args['systemCPU'], $args['systemRAM'], $args['systemOS'], $args['systemDomain']);
        }
        else
        {
            $hosts = HostOperator::search();
        }

        $data = array();

        foreach($hosts as $host)
        {
            $data[] = array(
                'id' => $host->getId(),
                'ipAddress' => $host->getIpAddress(),
                'macAddress' => $host->getMacAddress(),
                'assetTag' => AssetOperator::assetTagFromId($host->getAsset()),
                'systemName' => $host->getSystemName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }
}