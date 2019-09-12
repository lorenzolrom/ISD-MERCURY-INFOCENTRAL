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
use exceptions\EntryNotFoundException;
use models\Notification;
use models\User;
use utilities\Mailer;

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

    /**
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationError
     */
    public static function bulkSendToRoles(array $vals): array
    {
        self::validate('models\Notification', $vals);

        if(!isset($vals['roles']) OR !is_array($vals['roles']) OR empty($vals['roles']))
            return array('errors' => array('Roles not defined'));

        $email = FALSE;

        if(isset($vals['email']) AND ($vals['email'] == TRUE OR $vals['email'] == 1))
            $email = TRUE;

        $users = array();
        $userIds = array();

        foreach($vals['roles'] as $roleId)
        {
            try
            {
                $role = RoleOperator::getRole($roleId);

                foreach($role->getUsers() as $user)
                {
                    if(!in_array($user->getId(), $userIds))
                    {
                        $userIds[] = $user->getId();
                        $users[] = $user;
                    }
                }
            }
            catch(EntryNotFoundException $e){}
        }

        return array('count' => self::bulkSendToUsers($vals['title'], $vals['data'], $vals['important'], $email, $users));
    }

    /**
     * @param string $title
     * @param string $message
     * @param int $important
     * @param bool $email
     * @param User[] $users
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function bulkSendToUsers(string $title, string $message, int $important, bool $email, array $users): int
    {
        if(empty($users))
            return 0;

        $sendCount = 0;

        // Send email?
        if($email)
        {
            $mailer = new Mailer($title, $message, $users);
            $mailer->send();
        }

        foreach($users as $user)
        {
            NotificationDatabaseHandler::insert($user->getId(), $title, $message, $important);
            $sendCount++;
        }

        return $sendCount;
    }
}