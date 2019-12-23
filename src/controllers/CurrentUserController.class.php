<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/08/2019
 * Time: 2:16 PM
 */


namespace controllers;


use business\BulletinOperator;
use business\NotificationOperator;
use business\TokenOperator;
use business\UserOperator;
use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use exceptions\SecurityException;
use models\HTTPRequest;
use models\HTTPResponse;
use models\Token;
use models\User;

class CurrentUserController extends Controller
{
    /**
     * @return Token
     *
     * Gets the current session token (if it has been provided into a header)
     *
     * @throws DatabaseException
     * @throws SecurityException
     */
    public static function currentToken(): Token
    {
        try
        {
            if(isset($_SERVER['HTTP_TOKEN']))
            {
                $token = TokenOperator::getToken($_SERVER['HTTP_TOKEN']);
                TokenOperator::validateToken($token);

                return $token;
            }
        }
        catch (EntryNotFoundException $e){} // Handled below

        throw new SecurityException(SecurityException::MESSAGES[SecurityException::AUTHENTICATION_REQUIRED], SecurityException::AUTHENTICATION_REQUIRED);
    }

    /**
     * @return bool
     */
    public static function isTokenSupplied(): bool
    {
        return isset($_SERVER['HTTP_TOKEN']);
    }

    /**
     * @return User
     *
     * Gets the currently logged in user (if a Token has been supplied with the header)
     *
     * @throws DatabaseException
     * @throws SecurityException
     */
    public static function currentUser(): User
    {
        try
        {
            return UserOperator::getUser(self::currentToken()->getUser());
        }
        catch (EntryNotFoundException $e){} // Handled below

        throw new SecurityException(SecurityException::MESSAGES[SecurityException::AUTHENTICATION_REQUIRED], SecurityException::AUTHENTICATION_REQUIRED);
    }

    /**
     * @param string|array $permissionCode
     * @return bool
     * @throws DatabaseException
     * @throws SecurityException
     */
    public static function validatePermission($permissionCode): bool
    {
        try
        {
            $user = self::currentUser();
            $hasPerm = FALSE;
            $permissions = $user->getPermissions();

            if(!is_array($permissionCode) AND in_array($permissionCode, $permissions))
                $hasPerm = TRUE;
            else if(is_array($permissionCode))
            {
                foreach($permissionCode as $code)
                {
                    if(in_array($code, $permissions))
                        $hasPerm = TRUE;
                }
            }

            if(!$hasPerm)
                throw new SecurityException(SecurityException::MESSAGES[SecurityException::USER_NO_PERMISSION], SecurityException::USER_NO_PERMISSION);
        }
        catch(SecurityException $e)
        {
            // Only check for Secret permissions if the security fault was a user not being signed in
            if($e->getCode() !== SecurityException::AUTHENTICATION_REQUIRED)
                throw $e;

            return self::validateSecretPermission($permissionCode);
        }

        return TRUE;
    }

    /**
     * @param $permissionCode
     * @return bool
     * @throws DatabaseException
     * @throws SecurityException
     */
    public static function validateSecretPermission($permissionCode): bool
    {
        // Check if secret has permission
        $secret = FrontController::currentSecret();
        $permissions = $secret->getPermissions();

        if(!is_array($permissionCode) AND in_array($permissionCode, $permissions))
            return TRUE;
        else if(is_array($permissionCode))
        {
            foreach($permissionCode as $code)
            {
                if(in_array($code, $permissions))
                    return TRUE;
            }
        }

        throw new SecurityException(SecurityException::MESSAGES[SecurityException::KEY_NO_PERMISSION], SecurityException::KEY_NO_PERMISSION);
    }

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\LDAPException
     */
    public function getResponse(): ?HTTPResponse
    {
        if($this->request->method() == HTTPRequest::GET)
        {
            switch($this->request->next())
            {
                case "roles": return $this->getCurrentUserRoles();
                case "permissions": return $this->getCurrentUserPermissions();
                case "unreadNotificationCount": return $this->getUnreadNotificationCount();
                case "unreadNotifications": return $this->getUnreadNotifications();
                case "bulletins": return $this->getUserBulletins();
                case "notifications":
                    $param = $this->request->next();
                    switch($param)
                    {
                        case null:
                            return $this->getAllNotifications();
                        default:
                            return $this->getNotification($param);
                    }

                case null:
                    return $this->getCurrentUser();
            }
        }
        else if($this->request->method() == HTTPRequest::DELETE)
        {
            switch($this->request->next())
            {
                case "notifications":
                    return $this->deleteNotification($this->request->next());
            }
        }
        else if($this->request->method() == HTTPRequest::PUT)
        {
            switch($this->request->next())
            {
                case "changepassword":
                    return $this->changePassword();
            }
        }

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws DatabaseException
     * @throws SecurityException
     */
    private function getCurrentUser(): HTTPResponse
    {
        $user = self::currentUser();

        return new HTTPResponse(HTTPResponse::OK, array('id' => $user->getId(),
            'username' => $user->getUsername(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
            'authType' => $user->getAuthType()));
    }

    /**
     * @return HTTPResponse
     * @throws DatabaseException
     * @throws SecurityException
     */
    private function getCurrentUserRoles(): HTTPResponse
    {
        $user = self::currentUser();

        $roles = array();

        foreach($user->getRoles() as $role)
        {
            $roles[] = array(
                'id' => $role->getId(),
                'name' => $role->getName()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $roles);
    }

    /**
     * @return HTTPResponse
     * @throws DatabaseException
     * @throws SecurityException
     */
    private function getCurrentUserPermissions(): HTTPResponse
    {
        $user = self::currentUser();

        return new HTTPResponse(HTTPResponse::OK, $user->getPermissions());
    }

    /**
     * @return HTTPResponse
     * @throws DatabaseException
     * @throws SecurityException
     */
    private function getUnreadNotificationCount(): HTTPResponse
    {
        $user = self::currentUser();

        return new HTTPResponse(HTTPResponse::OK, array('count' => $user->getUnreadNotificationCount()));
    }

    /**
     * @return HTTPResponse
     * @throws DatabaseException
     * @throws SecurityException
     */
    private function getUnreadNotifications(): HTTPResponse
    {
        $user = self::currentUser();

        $data = array();

        foreach(NotificationOperator::getUserNotifications($user, array(0), array(0)) as $notification)
        {
            $data[] = array(
                'id' => $notification->getId(),
                'title' => $notification->getTitle(),
                'time' => $notification->getTime(),
                'data' => strip_tags($notification->getData()) // Strip tags just for cosmetic purposes
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @return HTTPResponse
     * @throws DatabaseException
     * @throws SecurityException
     */
    private function getAllNotifications(): HTTPResponse
    {
        $user = self::currentUser();

        $data = array();

        foreach(NotificationOperator::getUserNotifications($user, array(0, 1), array(0)) as $notification)
        {
            $data[] = array(
                'id' => $notification->getId(),
                'title' => $notification->getTitle(),
                'time' => $notification->getTime(),
                'data' => strip_tags($notification->getData()) // Strip tags just for cosmetic purposes
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws DatabaseException
     * @throws EntryNotFoundException
     * @throws SecurityException
     */
    private function getNotification(?string $param): HTTPResponse
    {
        $user = self::currentUser();

        $notification = NotificationOperator::viewNotification((int)$param);

        if($notification->getUser() != $user->getId())
            return new HTTPResponse(HTTPResponse::FORBIDDEN);

        $data = array(
            'id' => $notification->getId(),
            'title' => $notification->getTitle(),
            'time' => $notification->getTime(),
            'data' => $notification->getData()
        );

        return new HTTPResponse(HTTPResponse::OK, $data);
    }

    /**
     * @param string|null $param
     * @return HTTPResponse
     * @throws DatabaseException
     * @throws EntryNotFoundException
     * @throws SecurityException
     */
    private function deleteNotification(?string $param): HTTPResponse
    {
        self::currentUser();

        $notification = NotificationOperator::viewNotification((int)$param);

        NotificationOperator::deleteNotification($notification->getId());

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @return HTTPResponse
     * @throws DatabaseException
     * @throws SecurityException
     * @throws \exceptions\LDAPException
     * @throws EntryNotFoundException
     */
    private function changePassword(): HTTPResponse
    {
        $user = self::currentUser();

        $body = $this->request->body();

        $oldPassword = "";
        $newPassword = "";
        $confirmPassword = "";

        if(isset($body['old']))
            $oldPassword = $body['old'];
        if(isset($body['new']))
            $newPassword = $body['new'];
        if(isset($body['confirm']))
            $confirmPassword = $body['confirm'];

        // Change password
        $errors = UserOperator::changePassword($user, $oldPassword, $newPassword, $confirmPassword);

        if(!empty($errors))
            return new HTTPResponse(HTTPResponse::CONFLICT, $errors);

        return new HTTPResponse(HTTPResponse::NO_CONTENT);
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    private function getUserBulletins(): HTTPResponse
    {
        $data = array();

        foreach(BulletinOperator::getBulletinsByUser(CurrentUserController::currentUser()) as $bulletin)
        {
            $data[] = array(
                'title' => $bulletin->getTitle(),
                'message' => $bulletin->getMessage(),
                'type' => $bulletin->getType()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }
}