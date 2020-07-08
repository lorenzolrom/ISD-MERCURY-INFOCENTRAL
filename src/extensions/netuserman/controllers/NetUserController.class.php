<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 11/01/2019
 * Time: 1:10 PM
 */


namespace extensions\netuserman\controllers;


use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\LDAPException;
use exceptions\ValidationError;
use extensions\netuserman\business\NetUserOperator;
use extensions\netuserman\ExtConfig;
use models\HTTPRequest;
use models\HTTPResponse;

class NetUserController extends Controller
{

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\LDAPException
     * @throws \exceptions\SecurityException
     * @throws ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('netuserman-read');

        $param = $this->request->next();
        $next = $this->request->next();

        if($this->request->method() === HTTPRequest::GET)
        {
            if($param !== NULL)
            {
                if($next === 'photo')
                    return $this->getUserImage((string)$param);
                else if($next === NULL)
                    return $this->getUser((string)$param);
            }
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            if($param === 'search' AND $next === NULL)
            {
                return $this->searchUsers();
            }
            else if($next === 'photo')
            {
                CurrentUserController::validatePermission('netuserman-edit-details');
                return $this->updateUserImage((string)$param);
            }
            else if($param === NULL)
            {
                CurrentUserController::validatePermission('netuserman-create');
                return $this->createUser();
            }
        }
        else if($this->request->method() === HTTPRequest::PUT)
        {
            CurrentUserController::validatePermission('netuserman-edit-details');
            if($next === 'password')
                return $this->resetPassword((string)$param);
            else if($next === 'groups')
                return $this->modifyGroups((string)$param);
            return $this->updateUser((string)$param);
        }
        else if($this->request->method() === HTTPRequest::DELETE)
        {
            CurrentUserController::validatePermission('netuserman-delete');
            if($next === NULL AND $param !== NULL)
                return $this->deleteUser((string)$param);
        }

        return NULL;
    }

    /**
     * @param string $cn
     * @return HTTPResponse
     * @throws LDAPException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getUser(string $cn): HTTPResponse
    {
        return new HTTPResponse(HTTPResponse::OK, NetUserOperator::getUserDetails(urldecode($cn)));
    }

    /**
     * @param string $cn
     * @return HTTPResponse
     * @throws LDAPException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getUserImage(string $cn): HTTPResponse
    {
        $photo = NetUserOperator::getUserDetails(urldecode($cn), array('thumbnailPhoto'));

        if(!isset($photo['thumbnailphoto']) OR strlen($photo['thumbnailphoto']) === 0)
            $photo['thumbnailphoto'] = file_get_contents(dirname(__FILE__) . '/../media/no-photo-available.jpg');

        header('Content-Type: image/jpeg');
        echo $photo['thumbnailphoto'];
        exit;
    }

    /**
     * @param string $cn
     * @return HTTPResponse
     * @throws LDAPException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function updateUserImage(string $cn):HTTPResponse
    {
        if(empty($_FILES['thumbnailphoto']))
            throw new ValidationError(array('Photo required'));

        NetUserOperator::updateUserImage(urldecode($cn), file_get_contents($_FILES['thumbnailphoto']['tmp_name']));
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string $cn
     * @return HTTPResponse
     * @throws LDAPException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function updateUser(string $cn): HTTPResponse
    {
        $details = $this->request->body();

        NetUserOperator::updateUser(urldecode($cn), $details);
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\LDAPException
     */
    private function searchUsers(): HTTPResponse
    {
        $results = NetUserOperator::searchUsers(self::getFormattedBody(ExtConfig::OPTIONS['userSearchByAttributes'], TRUE));

        return new HTTPResponse(HTTPResponse::OK, $results);
    }

    /**
     * @param string $cn
     * @return HTTPResponse
     * @throws LDAPException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function resetPassword(string $cn): HTTPResponse
    {
        NetUserOperator::resetPassword(urldecode($cn), self::getFormattedBody(array('password', 'confirm'), TRUE));
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string $cn
     * @return HTTPResponse
     * @throws LDAPException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function modifyGroups(string $cn): HTTPResponse
    {
        NetUserOperator::modifyGroups(urldecode($cn), self::getFormattedBody(array('addGroups', 'removeGroups'), TRUE));
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string $cn
     * @return HTTPResponse
     * @throws LDAPException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function deleteUser(string $cn): HTTPResponse
    {
        NetUserOperator::deleteUser(urldecode($cn));
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @return HTTPResponse
     * @throws LDAPException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function createUser(): HTTPResponse
    {
        $result = NetUserOperator::createUser(self::getFormattedBody(array_merge(ExtConfig::OPTIONS['userEditableAttributes'], array('password', 'confirm')), TRUE));
        return new HTTPResponse(HTTPResponse::CREATED, $result);
    }
}
