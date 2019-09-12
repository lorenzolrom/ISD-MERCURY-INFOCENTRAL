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
use exceptions\EntryInUseException;
use exceptions\EntryNotFoundException;
use exceptions\LDAPException;
use exceptions\SecurityException;
use exceptions\ValidationError;
use exceptions\ValidationException;
use models\Role;
use models\Token;
use models\User;
use utilities\HistoryRecorder;
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
     * @return User
     * @throws DatabaseException
     * @throws EntryNotFoundException
     */
    public static function getUserByUsername(string $username): User
    {
        return UserDatabaseHandler::selectByUsername($username);
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
     * @param Role $role
     * @return array|User[]
     * @throws DatabaseException
     */
    public static function getByRole(Role $role)
    {
        return UserDatabaseHandler::selectByRole($role->getId());
    }

    /**
     * @param array $vals
     * @return array
     * @throws LDAPException
     * @throws DatabaseException
     * @throws EntryNotFoundException
     * @throws SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function createUser(array $vals): array
    {
        $errors = array();

        // Validate username format and that it is unique
        try{User::_validateUsername($vals['username']);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        try{User::_validateUsernameUnique($vals['username']);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        if(!empty($errors))
            throw new ValidationError($errors);

        if(isset($vals['authType']) AND $vals['authType'] == 'ldap') // LDAP user
        {
            $ldap = new LDAPConnection();
            $ldap->bind();

            // Verify LDAP username
            $results = $ldap->searchByUsername($vals['username'], array('givenname', 'sn', 'mail'));

            if($results['count'] !== 1)
            {
                throw new ValidationError(array('Username not found in directory'));
            }

            $results = $results[0];

            // Get name + email from LDAP
            $vals['firstName'] = isset($results['givenname'][0]) ? $results['givenname'][0] : '';
            $vals['lastName'] = isset($results['sn'][0]) ? $results['sn'][0] : '';
            $vals['email'] = isset($results['mail'][0]) ? $results['mail'][0] : '';
            $vals['password'] = NULL;
            $vals['disabled'] = 0; // User must be disabled through directory
        }
        else // Fallback - Local user
        {
            // Generic validation
            self::validate('models\User', $vals);

            // Password must exist for new local user
            try{User::_validatePassword($vals['password'], 'loca');}
            catch(ValidationException $e){throw new ValidationError(array($e->getMessage()));}

            // Hash password
            $vals['password'] = User::hashPassword($vals['password']);
        }

        $user = UserDatabaseHandler::insert($vals['username'], $vals['firstName'], $vals['lastName'], $vals['email'], $vals['password'], $vals['disabled'], $vals['authType']);
        $history = HistoryRecorder::writeHistory('User', HistoryRecorder::CREATE, $user->getId(), $user);

        if(is_array($vals['roles']))
        {
            HistoryRecorder::writeAssocHistory($history, array('roles' => $vals['roles']));
            UserDatabaseHandler::setRoles($user->getId(), $vals['roles']);
        }

        return array('id' => $user->getId());
    }

    /**
     * @param User $user
     * @param array $vals
     * @return array
     * @throws DatabaseException
     * @throws EntryNotFoundException
     * @throws LDAPException
     * @throws SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function updateUser(User $user, array $vals): array
    {
        $errors = array();

        // Validate username format and that it is unique
        try{User::_validateUsername($vals['username']);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // Check username if it hasn't been changed
        if(empty($errors) AND $user->getUsername() !== $vals['username'])
        {
            try{User::_validateUsernameUnique($vals['username']);}
            catch(ValidationException $e){$errors[] = $e->getMessage();}
        }

        if(!empty($errors))
            throw new ValidationError($errors);

        if(isset($vals['authType']) AND $vals['authType'] == 'ldap') // LDAP user
        {
            $ldap = new LDAPConnection();
            $ldap->bind();

            // Verify LDAP username
            $results = $ldap->searchByUsername($vals['username'], array('givenname', 'sn', 'mail'));

            if($results['count'] !== 1)
            {
                throw new ValidationError(array('Username not found in directory'));
            }

            $results = $results[0];

            // Get name + email from LDAP
            $vals['firstName'] = isset($results['givenname'][0]) ? $results['givenname'][0] : '';
            $vals['lastName'] = isset($results['sn'][0]) ? $results['sn'][0] : '';
            $vals['email'] = isset($results['mail'][0]) ? $results['mail'][0] : '';
            $vals['password'] = NULL;
            $vals['disabled'] = 0; // User must be disabled through directory
        }
        else // Fallback - Local user
        {
            // Generic validation
            $errors = self::validate('models\User', $vals);

            // Change password if it's been supplied
            if(isset($vals['password']) AND strlen($vals['password']) !== 0)
            {
                try{User::_validatePassword($vals['password'], 'loca');}
                catch(ValidationException $e){throw new ValidationError(array($e->getMessage()));}

                // Hash password
                UserDatabaseHandler::updatePassword($user->getId(), User::hashPassword($vals['password']));
            }

            if((!isset($vals['password']) OR strlen($vals['password']) === 0) AND ($user->getPassword() === NULL OR strlen($user->getPassword()) === 0))
                $errors[] = 'Password has not been set';

            if(is_array($errors) AND !empty($errors))
                throw new ValidationError($errors);
        }

        $history = HistoryRecorder::writeHistory('User', HistoryRecorder::MODIFY, $user->getId(), $user, $vals);
        $user = UserDatabaseHandler::update($user->getId(), $vals['username'], $vals['firstName'], $vals['lastName'], $vals['email'], $vals['disabled'], $vals['authType']);

        // Wipe password if user is LDAP
        if($vals['authType'] === 'ldap')
            UserDatabaseHandler::updatePassword($user->getId(), NULL);

        if(is_array($vals['roles']))
        {
            HistoryRecorder::writeAssocHistory($history, array('roles' => $vals['roles']));
            UserDatabaseHandler::setRoles($user->getId(), $vals['roles']);
        }

        return array('id' => $user->getId());
    }

    /**
     * @param User $user
     * @return bool
     * @throws DatabaseException
     * @throws EntryInUseException
     * @throws EntryNotFoundException
     * @throws SecurityException
     */
    public static function deleteUser(User $user): bool
    {
        try
        {
            UserDatabaseHandler::delete($user->getId());
        }
        catch(DatabaseException $e)
        {
            throw new EntryInUseException(EntryInUseException::MESSAGES[EntryInUseException::ENTRY_IN_USE], EntryInUseException::ENTRY_IN_USE);
        }

        HistoryRecorder::writeHistory('User', HistoryRecorder::DELETE, $user->getId(), $user);
        return TRUE;
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
        return password_verify(\Config::OPTIONS['salt'] . $password, $user->getPassword());
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