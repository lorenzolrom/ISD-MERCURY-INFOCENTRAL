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
use exceptions\RouteException;
use exceptions\ValidationError;
use extensions\netuserman\business\NetUserOperator;
use models\HTTPRequest;
use models\HTTPResponse;
use utilities\LDAPConnection;

class NetUserController extends Controller
{

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\LDAPException
     * @throws \exceptions\SecurityException
     * @throws RouteException
     * @throws ValidationError
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('netuserman');

        $param = $this->request->next();
        $next = $this->request->next();

        if($this->request->method() == HTTPRequest::GET)
        {
            CurrentUserController::validatePermission('netuserman-read');

            if($param !== NULL)
            {
                if($next === 'photo')
                    return $this->getUserImage((string)$param);
                else if($next === NULL)
                    return $this->getSingleUser((string)$param);
            }
        }
        else if($this->request->method() == HTTPRequest::POST)
        {
            CurrentUserController::validatePermission('netuserman-edit-details');

            if($next === 'photo')
            {
                return $this->updateUserImage((string)$param);
            }
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

        if(!isset($photo['thumbnailphoto']))
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
        if(!empty($_FILES['thumbnailphoto']))
        {
            $imageContents = file_get_contents($_FILES['thumbnailphoto']['tmp_name']);

            // File has content
            if(strlen($imageContents) === 0)
                throw new ValidationError(array('Photo required'));

            // File is type jpeg or jpg
            if(strtolower($_FILES['thumbnailphoto']['type']) !== 'image/jpeg')
                throw new ValidationError(array('Photo must be a JPEG'));

            // Set LDAP user thumbnailphoto
            $ldap = new LDAPConnection();
            if($ldap->setAttribute($username, 'thumbnailphoto', $imageContents))
                return new HTTPResponse(HTTPResponse::NO_CONTENT);
            else
                throw new ValidationError(array('Could not change photo'));
        }

        throw new ValidationError(array('Photo required'));
    }
}