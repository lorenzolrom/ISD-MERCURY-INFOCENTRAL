<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 10:51 PM
 */


namespace business;


use database\NotificationDatabaseHandler;
use models\Notification;
use models\User;

class NotificationOperator extends Operator
{
    /**
     * @param User $user
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function getUnreadCount(User $user): int
    {
        return NotificationDatabaseHandler::selectUnreadCountByUser($user->getId());
    }

    /**
     * @param int $id
     * @return Notification
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function viewNotification(int $id): Notification
    {
        $notification = NotificationDatabaseHandler::selectById($id, TRUE);

        // Mark notification as read
        NotificationDatabaseHandler::update($notification->getId(), 1, $notification->getDeleted());

        return $notification;
    }

    /**
     * @param int $id
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function deleteNotification(int $id): bool
    {
        $notification = NotificationDatabaseHandler::selectById($id);

        NotificationDatabaseHandler::update($notification->getId(), $notification->getRead(), 1);

        return TRUE;
    }

    /**
     * @param User $user
     * @param array $read
     * @param array $deleted
     * @param array $important
     * @return Notification[]
     * @throws \exceptions\DatabaseException
     */
    public static function getUserNotifications(User $user, $read = array(), $deleted = array(), $important = array()): array
    {
        return NotificationDatabaseHandler::selectByUser((int)$user->getId(), $read, $deleted, $important);
    }
}