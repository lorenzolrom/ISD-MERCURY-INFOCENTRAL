<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 4:20 PM
 */


namespace models;

use business\PermissionOperator;
use database\SecretDatabaseHandler;
use exceptions\ValidationException;
use utilities\Validator;

/**
 * Class Secret
 *
 * A secret key given to services using this API
 *
 * @package models
 */
class Secret extends Model
{
    private const NAME_RULES = array(
        'name' => 'Name',
        'lower' => 1,
        'upper' => 64
    );

    private $id;
    private $secret;
    private $name;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     * @throws \exceptions\DatabaseException
     */
    public function getPermissions(): array
    {
        $permissions = array();

        foreach(PermissionOperator::getSecretPermissions($this) as $permission)
        {
            if(!in_array($permission->getCode(), $permissions))
                $permissions[] = $permission->getCode();
        }

        return $permissions;
    }

    /**
     * @param string|null $name
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateName(?string $name): bool
    {
        Validator::validate(self::NAME_RULES, $name);

        if(SecretDatabaseHandler::selectIdFromName($name) !== NULL)
            throw new ValidationException('Name already in use', ValidationException::VALUE_ALREADY_TAKEN);

        return TRUE;
    }
}