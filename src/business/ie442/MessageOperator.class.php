<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 10/21/2019
 * Time: 5:13 PM
 */


namespace business\ie442;


use business\Operator;
use controllers\CurrentUserController;
use database\ie442\MessageDatabaseHandler;
use models\ie442\Message;

class MessageOperator extends Operator
{
    const DEFAULT_MINUTES = 10; // Default number of minutes to show prior messages from

    /**
     * @param int|null $room
     * @param string $message
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function sendMessage(int $room, string $message): bool
    {
        // Convert '0' room to null
        if($room === 0)
            $room = NULL;

        MessageDatabaseHandler::insert(CurrentUserController::currentUser()->getId(), $room, $message);

        return TRUE;
    }

    /**
     * @param int $room
     * @return Message[]
     * @throws \exceptions\DatabaseException
     */
    public static function getMessages(int $room): array
    {
        if($room === 0)
            $room = NULL;

        return MessageDatabaseHandler::selectByRoom($room, self::DEFAULT_MINUTES);
    }
}