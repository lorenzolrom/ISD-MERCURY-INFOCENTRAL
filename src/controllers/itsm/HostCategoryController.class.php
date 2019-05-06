<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/06/2019
 * Time: 12:59 AM
 */


namespace controllers\itsm;


use business\itsm\HostCategoryOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class HostCategoryController extends Controller
{

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws \exceptions\SecurityException
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

        $data = array();

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

            $data[] = $hostInfo;
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }
}