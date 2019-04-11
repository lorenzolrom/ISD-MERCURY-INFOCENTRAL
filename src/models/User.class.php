<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/06/2019
 * Time: 3:36 PM
 */


namespace models;


use business\NotificationOperator;
use business\RoleOperator;

class User extends Model
{
    private $id;
    private $username;
    private $firstName;
    private $lastName;
    private $email;
    private $password;
    private $disabled;
    private $authType;

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
    public function getUsername(): string
    {
        return $this->username;
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
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return int
     */
    public function getDisabled(): int
    {
        return $this->disabled;
    }

    /**
     * @return string
     */
    public function getAuthType(): string
    {
        return $this->authType;
    }

    /**
     * @return Role[]
     * @throws \exceptions\DatabaseException
     */
    public function getRoles(): array
    {
        return RoleOperator::getUserRoles($this);
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public function getPermissions(): array
    {
        $permissions = array();

        foreach(RoleOperator::getUserRoles($this) as $role)
        {
            foreach($role->getPermissions() as $permission)
            {
                if(!in_array($permission->getCode(), $permissions))
                    $permissions[] = $permission->getCode();
            }
        }

        return $permissions;
    }

    /**
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public function getUnreadNotificationCount(): int
    {
        return NotificationOperator::getUnreadCount($this);
    }

    /**
     * @param string $password
     * @return string
     */
    public static function hashPassword(string $password): string
    {
        // TODO: replace with ARGON2 ID salt once account management is migrated to MERLOT app
        return hash('SHA512', $password);
    }
}