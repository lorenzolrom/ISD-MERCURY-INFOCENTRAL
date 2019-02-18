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
    public function __construct(int $id, string $loginName, string $authType, string $password, string $firstName,
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
}