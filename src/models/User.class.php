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
use business\UserOperator;
use exceptions\ValidationException;
use utilities\Validator;

class User extends Model
{
    private const USERNAME_RULES = array(
        'name' => 'Username',
        'lower' => 1,
        'upper' => 64
    );

    private const FIRST_NAME_RULES = array(
        'name' => 'First name',
        'lower' => 1,
        'upper' => 30
    );

    private const LAST_NAME_RULES = array(
        'name' => 'Last name',
        'lower' => 1,
        'upper' => 30
    );

    private const EMAIL_RULES = array(
        'name' => 'Email',
        'type' => 'email',
        'empty' => TRUE
    );

    private const DISABLED_RULES = array(
        'name' => 'Disabled',
        'acceptable' => array(0, 1)
    );

    private const AUTH_TYPE_RULES = array(
        'name' => 'Auth Type',
        'acceptable' => array('loca', 'ldap')
    );

    private const PASSWORD_RULES = array(
        'name' => 'Password',
        'lower' => 8
    );

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
        return password_hash(\Config::OPTIONS['salt'] . $password, PASSWORD_ARGON2I);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function _validateUsername(?string $val): bool
    {
        return Validator::validate(self::USERNAME_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateFirstName(?string $val): bool
    {
        return Validator::validate(self::FIRST_NAME_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateLastName(?string $val): bool
    {
        return Validator::validate(self::LAST_NAME_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateEmail(?string $val): bool
    {
        return Validator::validate(self::EMAIL_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateDisabled(?string $val): bool
    {
        return Validator::validate(self::DISABLED_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateAuthType(?string $val): bool
    {
        return Validator::validate(self::AUTH_TYPE_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function _validateUsernameUnique(?string $val): bool
    {
        if(UserOperator::idFromUsername($val) !== NULL)
            throw new ValidationException('Username already in use', ValidationException::VALUE_ALREADY_TAKEN);

        return TRUE;
    }

    /**
     * Determine if the input for password is accurate.
     * Password NOT required if authType is 'ldap'
     * Password required if authType is 'loca', must check when switching between LDAP and local accounts
     *
     * Underscore is to prevent automatic validation
     *
     * @param string|null $password
     * @param string|null $authType
     * @param User|null $user
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function _validatePassword(?string $password, ?string $authType = NULL, ?User $user = NULL): bool
    {
        if($authType === 'ldap') // auth type for new user is ldap
            return TRUE;

        if($user !== NULL AND $user->getAuthType() === 'ldap') // existing user is ldap
            return TRUE;

        if($user !== NULL AND $user->getAuthType() === 'loca' AND ($user->getPassword() === NULL OR strlen($user->getPassword()) === 0))
            throw new ValidationException('Password has not been set', ValidationException::VALUE_IS_NOT_VALID);

        // Default to Validator (is set and greater than 8 characters)
        return Validator::validate(self::PASSWORD_RULES, $password);
    }
}