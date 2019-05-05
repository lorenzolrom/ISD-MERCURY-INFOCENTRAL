<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 10:46 AM
 */


namespace models;


use business\PermissionOperator;
use business\UserOperator;
use database\RoleDatabaseHandler;
use exceptions\ValidationException;
use utilities\Validator;

class Role extends Model
{
    private const NAME_RULES = array(
        'name' => 'Name',
        'lower' => 1,
        'upper' => 64
    );

    private const NAME_IN_USE = 'Name already in use';

    private $id;
    private $name;

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Permission[]
     * @throws \exceptions\DatabaseException
     */
    public function getPermissions(): array
    {
        return PermissionOperator::getRolePermissions($this);
    }

    /**
     * @return User[]
     * @throws \exceptions\DatabaseException
     */
    public function getUsers(): array
    {
        return UserOperator::getByRole($this);
    }

    /**
     * @param string|null $name
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateName(?string $name): bool
    {
        Validator::validate(self::NAME_RULES, $name);

        if(RoleDatabaseHandler::selectIdFromName($name) !== NULL)
            throw new ValidationException(self::NAME_IN_USE, ValidationException::VALUE_ALREADY_TAKEN);

        return TRUE;
    }
}