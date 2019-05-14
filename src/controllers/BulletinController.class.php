<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/04/2019
 * Time: 4:00 PM
 */


namespace controllers;


use business\BulletinOperator;
use exceptions\EntryNotFoundException;
use models\HTTPRequest;
use models\HTTPResponse;

class BulletinController extends Controller
{
    private const FIELDS = array('startDate', 'endDate', 'title','message', 'inactive', 'type', 'roles');

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
        CurrentUserController::validatePermission('settings');

        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            switch($param)
            {
                case null:
                    return $this->search();
                default:
                    return $this->getBulletin($param);
            }
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            switch($param)
            {
                case 'search':
                    return $this->search(TRUE);
                default:
                    return $this->createBulletin();
            }
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            return $this->updateBulletin($param);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            return $this->deleteBulletin($param);
        }

        return NULL;
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    private function getBulletin(?string $param): HTTPResponse
    {
        $bulletin = BulletinOperator::getBulletin((int) $param);

        $roles = array();

        foreach(BulletinOperator::getRoles($bulletin) as $role)
        {
            $roles[] = array(
                'id' => $role->getId(),
                'name' => $role->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, array(
            'id' => $bulletin->getId(),
            'startDate' => $bulletin->getStartDate(),
            'endDate' => $bulletin->getEndDate(),
            'title' => $bulletin->getTitle(),
            'message' => $bulletin->getMessage(),
            'inactive' => $bulletin->getInactive(),
            'type' => $bulletin->getType(),
            'roles' => $roles
        ));
    }

    /**
     * @param bool $search
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function search(bool $search = FALSE): HTTPResponse
    {
        $data = array();
        $args = NULL;

        if($search)
            $args = self::getFormattedBody(self::FIELDS);

        foreach(BulletinOperator::search($args) as $bulletin)
        {
            $data[] = array(
                'id' => $bulletin->getId(),
                'startDate' => $bulletin->getStartDate(),
                'endDate' => $bulletin->getEndDate(),
                'title' => $bulletin->getTitle(),
                'inactive' => $bulletin->getInactive(),
                'type' => $bulletin->getType()
            );
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
    private function createBulletin(): HTTPResponse
    {
        $args = self::getFormattedBody(self::FIELDS);

        return new HTTPResponse(HTTPResponse::CREATED, BulletinOperator::createBulletin($args));
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    private function updateBulletin(?string $param): HTTPResponse
    {
        $bulletin = BulletinOperator::getBulletin((int) $param);
        $args = self::getFormattedBody(self::FIELDS);
        BulletinOperator::updateBulletin($bulletin, $args);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function deleteBulletin(?string $param): HTTPResponse
    {
        $bulletin = BulletinOperator::getBulletin((int) $param);

        BulletinOperator::deleteBulletin($bulletin);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }
}