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

/**
 * Class Secret
 *
 * A secret key given to services using this API
 *
 * @package models
 */
class Secret
{
    private $secret;
    private $name;

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
}