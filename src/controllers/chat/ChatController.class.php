<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 10/25/2019
 * Time: 10:20 AM
 */


namespace controllers\chat;


use business\chat\HeartbeatOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use exceptions\EntryInUseException;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use models\HTTPRequest;
use models\HTTPResponse;

class ChatController extends Controller
{

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('chat');

        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::PUT)
        {
            if($param == 'heartbeat')
            {
                return $this->updateHeartbeat();
            }
        }
        else if($this->request->method() === HTTPRequest::GET)
        {
            if($param == 'activeUsers')
            {
                return $this->getActiveUsers();
            }
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function updateHeartbeat(): HTTPResponse
    {
        $user = CurrentUserController::currentUser();

        HeartbeatOperator::updateHeartbeat($user);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getActiveUsers(): HTTPResponse
    {
        $userData = array();

        foreach(HeartbeatOperator::getActiveUsers() as $user)
        {
            $userData[] = array(
                'username' => $user->getUsername(),
                'name' => $user->getFirstName() . ' ' .$user->getLastName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $userData);
    }
}