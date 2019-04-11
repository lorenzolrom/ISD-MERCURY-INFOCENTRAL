<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/10/2019
 * Time: 10:42 PM
 */


namespace controllers\bookworm;


use controllers\Controller;
use controllers\CurrentUserController;
use models\HTTPRequest;
use models\HTTPResponse;
use utilities\GoogleAPIConnection;

class VolumeController extends Controller
{

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission("bookworm_volumes-r");

        if($this->request->method() == HTTPRequest::POST)
        {
            switch($this->request->next())
            {
                case "search":
                    return $this->search();
            }
        }
        else if($this->request->method() == HTTPRequest::GET)
        {
            return $this->getVolume($this->request->next());
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     */
    private function search(): HTTPResponse
    {
        $body = $this->request->body();

        if(!isset($body['title']) OR strlen($body['title']) === 0)
            return new HTTPResponse(HTTPResponse::BAD_REQUEST, array('errors' => array('Title is required for volume search')));

        $results = GoogleAPIConnection::searchTitle($body['title']);

        $volumes = array();

        if(isset($results['items']))
            $volumes = $results['items'];

        return new HTTPResponse(HTTPResponse::OK, array('volumes' => $volumes));
    }

    /**
     * @param string|null $volumeId
     * @return HTTPResponse
     */
    private function getVolume(?string $volumeId): HTTPResponse
    {
        if($volumeId === NULL)
            return new HTTPResponse(HTTPResponse::BAD_REQUEST, array('errors' => array('Volume id was not supplied')));

        $results = GoogleAPIConnection::getVolume($volumeId);

        return new HTTPResponse(HTTPResponse::OK, $results);
    }
}