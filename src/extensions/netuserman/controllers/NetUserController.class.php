<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
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
                    return $this->getSingleUser((string)$param);
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
     * @param string $username
     * @return HTTPResponse
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\LDAPException
     */
    private function getSingleUser(string $username): HTTPResponse
    {
        return new HTTPResponse(HTTPResponse::OK, NetUserOperator::getUserDetails($username));
    }

    /**
     * @param string $username
     * @return HTTPResponse
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\LDAPException
     */
    private function getUserImage(string $username): HTTPResponse
    {
        $photo = NetUserOperator::getUserDetails($username, array('thumbnailPhoto'));

        if(!isset($photo['thumbnailphoto']) OR strlen($photo['thumbnailphoto']) === 0)
            $photo['thumbnailphoto'] = file_get_contents(dirname(__FILE__) . '/../media/no-photo-available.jpg');

        header('Content-Type: image/jpeg');
        echo $photo['thumbnailphoto'];
        exit;
    }

    /**
     * @param string $username
     * @return HTTPResponse
     * @throws ValidationError
     * @throws \exceptions\LDAPException
     */
    private function updateUserImage(string $username):HTTPResponse
    {
        if(empty($_FILES['thumbnailphoto']))
            throw new ValidationError(array('Photo required'));

        NetUserOperator::updateUserImage($username, file_get_contents($_FILES['thumbnailphoto']['tmp_name']));
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string $username
     * @return HTTPResponse
     * @throws LDAPException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function updateUser(string $username): HTTPResponse
    {
        $details = $this->request->body();

        NetUserOperator::updateUser($username, $details);
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
     * @param string $username
     * @return HTTPResponse
     * @throws LDAPException
     * @throws ValidationError
     */
    private function resetPassword(string $username): HTTPResponse
    {
        NetUserOperator::resetPassword($username, self::getFormattedBody(array('password', 'confirm'), TRUE));
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string $username
     * @return HTTPResponse
     * @throws LDAPException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function modifyGroups(string $username): HTTPResponse
    {
        NetUserOperator::modifyGroups($username, self::getFormattedBody(array('addGroups', 'removeGroups'), TRUE));
        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @param string $username
     * @return HTTPResponse
     * @throws LDAPException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function deleteUser(string $username): HTTPResponse
    {
        NetUserOperator::deleteUser($username);
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