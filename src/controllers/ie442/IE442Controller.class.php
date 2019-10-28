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


namespace controllers\ie442;


use business\ie442\HeartbeatOperator;
use business\ie442\MessageOperator;
use business\UserOperator;
use controllers\Controller;
use controllers\CurrentUserController;
use models\HTTPRequest;
use models\HTTPResponse;

class IE442Controller extends Controller
{

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\EntryNotFoundException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('game');

        $param = $this->request->next();

        if($this->request->method() === HTTPRequest::PUT)
        {
            if($param == 'heartbeat')
                return $this->updateHeartbeat();
        }
        else if($this->request->method() === HTTPRequest::GET)
        {
            if($param == 'activeUsers')
                return $this->getActiveUsers();
            else if($param == 'chatUsers')
                return $this->getChatUsers();
            else if($param == 'messages')
                return $this->getMessages((int)$this->request->next());
        }
        else if($this->request->method() === HTTPRequest::POST)
        {
            if($param == 'messages')
                return $this->sendMessage();
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

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function getChatUsers(): HTTPResponse
    {
        $userData = array();

        foreach(HeartbeatOperator::getChatUsers() as $user)
        {
            $userData[] = array(
                'username' => $user->getUsername(),
                'name' => $user->getFirstName() . ' ' .$user->getLastName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $userData);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    private function sendMessage(): HTTPResponse
    {
        $args = self::getFormattedBody(['room', 'message']);

        // TODO check if user is in room if room is not 0

        MessageOperator::sendMessage((int)$args['room'], (string)$args['message']);

        return new HTTPResponse(HTTPResponse::CREATED);
    }

    /**
     * @param int $room
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    private function getMessages(int $room): HTTPResponse
    {
        $messages = MessageOperator::getMessages((int)$room);
        $data = array();

        foreach($messages as $message)
        {
            $user = UserOperator::getUser($message->getUser());

            $data[] = array(
                'id' => $message->getId(),
                'username' => $user->getUsername(),
                'time' => $message->getTime(),
                'message' => $message->getMessage()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }
}