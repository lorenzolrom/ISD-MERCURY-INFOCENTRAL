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


namespace business\chat;


use business\Operator;
use business\UserOperator;
use database\chat\RoomDatabaseHandler;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use models\chat\Room;
use utilities\HistoryRecorder;

class RoomOperator extends Operator
{
    /**
     * Create a new chat room
     *
     * @param array $users An array of int User(id).  If no valid users are given, a validation error will be generated
     * @param int $private Should this chat room be private? 1 = yes 0 = no
     * @param string|null $title Optional title
     * @return Room The new room object
     * @throws EntryNotFoundException The room could not be created
     * @throws ValidationError No users could be added
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function createRoom(array $users, int $private = 0, ?string $title = NULL): Room
    {
        // Check if title is already in use, if one is not specified
        if($title !== NULL AND RoomDatabaseHandler::isTitleInUse($title))
            throw new ValidationError(array('Room already exists with title'));

        // Create room in database
        $room = RoomDatabaseHandler::insert($title, $private, 0);

        $hist = HistoryRecorder::writeHistory('Chat_Room', HistoryRecorder::CREATE, $room->getId(), $room);

        $userObjs = array();

        // Add user objects
        foreach($users as $id)
        {
            try
            {
                $userObjs[] = UserOperator::getUser((int)$id);
                HistoryRecorder::writeAssocHistory($hist, array('addUser' => array($id)));
            }
            catch(EntryNotFoundException $e){} // do not add user
        }

        if(count($userObjs) === 0) //  if no valid users were provided
        {
            throw new ValidationError(array('No users could be added to the Room'));
        }

        // Proceed with user adds
        foreach($userObjs as $user)
        {
            RoomDatabaseHandler::insertUser($room->getId(), $user->getId());
        }

        return $room;
    }

    public static function makeRoomPrivate(Room $room): bool
    {
        $errors = array();

        // Is the chat room already private?
        if($room->getPrivate() === 1)
            $errors[] = 'Room is already private';

        // Is current user a member of the chat room?
    }
}