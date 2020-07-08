<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 5/06/2019
 * Time: 12:59 AM
 */


namespace extensions\itsm\controllers;


use extensions\itsm\business\HostCategoryOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class HostCategoryController extends Controller
{
    private const FIELDS = array('name', 'displayed', 'hosts');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     * @throws \exceptions\ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('itsmmonitor-hosts-r');

        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            switch($param)
            {
                case null:
                    return $this->getCategories();
                case 'displayed':
                    return $this->getCategories(TRUE);
                default:
                    switch($this->request->next())
                    {
                        case null:
                            return $this->getHosts($param);
                        case 'status':
                            return $this->getHosts($param, TRUE);
                    }
            }
        }
        else if($this->request->method() === HTTPRequest::POST)
            return $this->create();
        else if($this->request->method() === HTTPRequest::PUT)
            return $this->update($param);
        else if($this->request->method() === HTTPRequest::DELETE)
            return $this->delete($param);

        return NULL;
    }

    /**
     * @param bool $displayedOnly
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getCategories(bool $displayedOnly = FALSE): HTTPResponse
    {
        if($displayedOnly)
            $categories = HostCategoryOperator::getDisplayed();
        else
            $categories = HostCategoryOperator::getAll();

        $data = array();

        foreach($categories as $category)
        {
            $data[] = array(
                'id' => $category->getId(),
                'name' => $category->getName(),
                'displayed' => $category->getDisplayed()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string|null $param
     * @param bool $showStatus
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getHosts(?string $param, bool $showStatus = FALSE)
    {
        $category = HostCategoryOperator::getCategory((int) $param);

        $data = array(
            'id' => $category->getId(),
            'name' => $category->getName(),
            'displayed' => $category->getDisplayed(),
            'hosts' => array()
        );

        foreach($category->getHosts() as $host)
        {
            $hostInfo = array(
                'id' => $host->getId(),
                'ipAddress' => $host->getIpAddress(),
                'systemName' => $host->getSystemName()
            );

            if($showStatus)
            {
                $hostInfo['status'] = $host->isOnline() ? 'online' : 'offline';
            }

            $data['hosts'][] = $hostInfo;
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function create(): HTTPResponse
    {
        CurrentUserController::validatePermission('itsmmonitor-hosts-w');

        return new HTTPResponse(HTTPResponse::CREATED, HostCategoryOperator::create(self::getFormattedBody(self::FIELDS)));
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function update(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission('itsmmonitor-hosts-w');
        $category = HostCategoryOperator::getCategory((int) $param);
        HostCategoryOperator::update($category, self::getFormattedBody(self::FIELDS));

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function delete(?string $param): HTTPResponse
    {
        CurrentUserController::validatePermission('itsmmonitor-hosts-w');

        $category = HostCategoryOperator::getCategory((int) $param);
        HostCategoryOperator::delete($category);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}
