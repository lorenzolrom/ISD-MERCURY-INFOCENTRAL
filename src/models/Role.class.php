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

class Role extends Model
{
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
}