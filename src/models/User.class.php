<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 2:46 PM
 */


namespace models;


use database\UserDatabaseHandler;
use factories\RoleFactory;
use messages\ValidationError;

class User
{
    private $id;
    private $loginName;
    private $authType;
    private $password;
    private $firstName;
    private $lastName;
    private $displayName;
    private $email;
    private $disabled;

    /**
     * User constructor.
     * @param int $id
     * @param string $loginName
     * @param string $authType
     * @param string $password
     * @param string $firstName
     * @param string $lastName
     * @param string|null $displayName
     * @param string|null $email
     * @param string $disabled
     */
    public function __construct(int $id, string $loginName, string $authType, ?string $password, string $firstName,
                                string $lastName, ?string $displayName, ?string $email, string $disabled)
    {
        $this->id = $id;
        $this->loginName = $loginName;
        $this->authType = $authType;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->displayName = $displayName;
        $this->email = $email;
        $this->disabled = $disabled;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLoginName(): string
    {
        return $this->loginName;
    }

    /**
     * @return string
     */
    public function getAuthType(): string
    {
        return $this->authType;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getDisabled(): string
    {
        return $this->disabled;
    }

    /**
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public function delete()
    {
        return UserDatabaseHandler::delete($this->id);
    }

    /**
     * Marks all tokens for this user as expired
     * @throws \exceptions\DatabaseException
     */
    public function expireAllTokens()
    {
        UserDatabaseHandler::expireAllTokensForUser($this->id);
    }

    /**
     * @return Role[]
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public function getRoles(): array
    {
        $roles = array();

        $roleIDs = UserDatabaseHandler::selectUserRoleIDs($this->id);

        foreach($roleIDs as $roleID)
        {
            $roles[] = RoleFactory::getFromID($roleID);
        }

        return $roles;
    }

    /**
     * @param string $permissionCode
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public function hasPermission(string $permissionCode): bool
    {
        foreach($this->getRoles() as $role)
        {
            if(in_array($permissionCode, $role->getPermissionCodes()))
                return TRUE;
        }

        return FALSE;
    }

    /**
     * @throws \exceptions\DatabaseException
     */
    public function logout()
    {
        UserDatabaseHandler::expireAllTokensForUser($this->id);
    }

    /**
     * @param int $roleID
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public function addRole(int $roleID): bool
    {
        return UserDatabaseHandler::addRoleToUser($this->id, $roleID);
    }

    /**
     * @param int $roleID
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public function removeRole(int $roleID): bool
    {
        return UserDatabaseHandler::removeRoleFromUser($this->id, $roleID);
    }

    /**
     * @param string|null $loginName
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function validateLoginName(?string $loginName): int
    {
        // Is not null
        if($loginName === NULL)
            return ValidationError::VALUE_IS_NULL;

        // Is between 1 and 64 characters
        if(strlen($loginName) < 1)
            return ValidationError::VALUE_IS_TOO_SHORT;

        if(strlen($loginName) > 64)
            return ValidationError::VALUE_IS_TOO_LONG;

        // Is not already taken
        if(UserDatabaseHandler::isLoginNameTaken($loginName))
            return ValidationError::VALUE_ALREADY_TAKEN;

        return ValidationError::VALUE_IS_OK;
    }

    /**
     * @param string|null $authType
     * @return int
     */
    public static function validateAuthType(?string $authType): int
    {
        // Is not null
        if($authType === NULL)
            return ValidationError::VALUE_IS_NULL;

        // Is valid
        if(!in_array($authType, ['local', 'ldap']))
            return ValidationError::VALUE_IS_INVALID;

        return ValidationError::VALUE_IS_OK;
    }

    /**
     * Validator for first and last name
     * @param string|null $name
     * @return int
     */
    public static function validateXName(?string $name): int
    {
        // Is not null
        if($name === NULL)
            return ValidationError::VALUE_IS_NULL;

        // Is between 1 and 32 characters
        if(strlen($name) < 1)
            return ValidationError::VALUE_IS_TOO_SHORT;
        if(strlen($name) > 64)
            return ValidationError::VALUE_IS_TOO_LONG;

        return ValidationError::VALUE_IS_OK;
    }

    /**
     * @param string|null $email
     * @return int
     */
    public static function validateEmail(?string $email): int
    {
        if($email !== NULL AND !filter_var($email, FILTER_VALIDATE_EMAIL))
            return ValidationError::VALUE_IS_INVALID;

        return ValidationError::VALUE_IS_OK;
    }

    /**
     * @param int|null $disabled
     * @return int
     */
    public static function validateDisabled(?int $disabled): int
    {
        if($disabled === NULL)
            return ValidationError::VALUE_IS_NULL;

        if($disabled != 1 AND $disabled != 0)
            return ValidationError::VALUE_IS_INVALID;

        return ValidationError::VALUE_IS_OK;
    }

    /**
     * @param string|null $password
     * @return int
     */
    public static function validatePassword(?string $password): int
    {
        if($password === NULL)
            return ValidationError::VALUE_IS_NULL;

        if(strlen($password) < 8)
            return ValidationError::VALUE_IS_TOO_SHORT;

        return ValidationError::VALUE_IS_OK;
    }
}