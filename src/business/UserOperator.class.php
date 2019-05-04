<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 9:28 AM
 */


namespace business;


use database\TokenDatabaseHandler;
use database\UserDatabaseHandler;
use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use exceptions\LDAPException;
use exceptions\SecurityException;
use models\Token;
use models\User;
use utilities\LDAPConnection;

class UserOperator extends Operator
{
    /**
     * @param int $id
     * @return User
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getUser(int $id): User
    {
        return UserDatabaseHandler::selectById($id);
    }

    /**
     * @param string $username
     * @param string $firstName
     * @param string $lastName
     * @param array $disabled
     * @return User[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(string $username = "%", string $firstName = "%", string $lastName = "%", $disabled = array()): array
    {
        return UserDatabaseHandler::select($username, $firstName, $lastName, $disabled);
    }

    /**
     * @param string $username
     * @param string $password
     * @return Token
     * @throws SecurityException
     * @throws \exceptions\DatabaseException
     * @throws LDAPException
     */
    public static function loginUser(string $username, string $password): Token
    {
        try
        {
            $user = UserDatabaseHandler::selectByUsername($username);

            self::authenticateUser($username, $password);

            // If this option is enabled, only allow login from one location
            if(!isset(\Config::OPTIONS['allowMultipleSessions']) OR \Config::OPTIONS['allowMultipleSessions'] === FALSE)
            {
                TokenDatabaseHandler::markExpiredForUser($user->getId());
            }

            return TokenOperator::generateNewToken($user->getId());
        }
        catch(EntryNotFoundException $e)
        {
            throw new SecurityException(SecurityException::MESSAGES[SecurityException::USER_NOT_FOUND], SecurityException::USER_NOT_FOUND);
        }
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool
     * @throws DatabaseException
     * @throws LDAPException
     * @throws SecurityException
     */
    public static function authenticateUser(string $username, string $password): bool
    {
        try
        {
            $user = UserDatabaseHandler::selectByUsername($username);

            // Check for disabled user
            if($user->getDisabled() == 1)
                throw new SecurityException(SecurityException::MESSAGES[SecurityException::USER_IS_DISABLED], SecurityException::USER_IS_DISABLED);

            // Authenticate user credentials
            switch($user->getAuthType())
            {
                case 'ldap':
                    $ok = self::authenticateLDAPUser($user, $password);
                    break;
                default:
                    $ok = self::authenticateLocalUser($user, $password);
            }

            if(!$ok)
                throw new SecurityException(SecurityException::MESSAGES[SecurityException::USER_PASSWORD_INCORRECT], SecurityException::USER_PASSWORD_INCORRECT);

            return TRUE;
        }
        catch(EntryNotFoundException $e)
        {
            throw new SecurityException(SecurityException::MESSAGES[SecurityException::USER_NOT_FOUND], SecurityException::USER_NOT_FOUND);
        }
    }

    /**
     * @param User $user
     * @param string $oldPassword
     * @param string $newPassword
     * @param string $confirmPassword
     * @return array
     * @throws DatabaseException
     * @throws LDAPException
     * @throws EntryNotFoundException
     */
    public static function changePassword(User $user, string $oldPassword, string $newPassword, string $confirmPassword): array
    {
        $errors = array();

        // Check current password
        try
        {
            UserOperator::authenticateUser($user->getUsername(), $oldPassword);
        }
        catch (SecurityException $e)
        {
            $errors[] = "Current password is incorrect";
        }

        // Verify new password is greater than 8 characters
        if(strlen($newPassword) < 8)
            $errors[] = "New password must be at least 8 characters";

        // Verify new and confirm match
        if($newPassword != $confirmPassword)
            $errors[] = "New passwords do not match";

        // Return validation errors if they exist
        if(!empty($errors))
            return $errors;

        switch($user->getAuthType())
        {
            case 'ldap':
                self::changeLDAPUserPassword($user, $newPassword);
                break;
            default:
                self::changeLocalUserPassword($user, $newPassword);
        }

        return $errors;
    }

    /**
     * @param int $id
     * @return string|null
     * @throws DatabaseException
     */
    public static function usernameFromId(int $id): ?string
    {
        return UserDatabaseHandler::selectUsernameFromId($id);
    }

    /**
     * @param string $username
     * @return int|null
     * @throws DatabaseException
     */
    public static function idFromUsername(?string $username): ?int
    {
        return UserDatabaseHandler::selectIdFromUsername((string)$username);
    }

    /**
     * @param User $user
     * @param string $password
     * @return bool
     */
    private static function authenticateLocalUser(User $user, string $password): bool
    {
        if(User::hashPassword($password) == $user->getPassword())
            return TRUE;

        return FALSE;
    }

    /**
     * @param User $user
     * @param string $password
     * @return bool
     * @throws \exceptions\LDAPException
     */
    private static function authenticateLDAPUser(User $user, string $password): bool
    {
        $ldap = new LDAPConnection();

        if(strlen($password) == 0)
            return FALSE;

        if($ldap->bind($user->getUsername(), $password))
            return TRUE;

        return FALSE;
    }

    /**
     * @param User $user
     * @param string $password
     * @return bool
     * @throws DatabaseException
     * @throws EntryNotFoundException
     */
    private static function changeLocalUserPassword(User $user, string $password): bool
    {
        UserDatabaseHandler::updatePassword($user->getId(), User::hashPassword($password));
        return TRUE;
    }

    /**
     * @param User $user
     * @param string $password
     * @return bool
     * @throws LDAPException
     */
    private static function changeLDAPUserPassword(User $user, string $password): bool
    {
        $ldap = new LDAPConnection();
        $ldap->setPassword($user->getUsername(), $password);
        return TRUE;
    }
}