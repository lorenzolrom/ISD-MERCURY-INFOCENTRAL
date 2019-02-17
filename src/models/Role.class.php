<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 4:11 PM
 */


namespace models;


use database\RoleDatabaseHandler;

class Role extends Model
{
    private $id;
    private $displayName;

    /**
     * Role constructor.
     * @param int $id
     * @param string $displayName
     */
    public function __construct(int $id, string $displayName)
    {
        $this->id = $id;
        $this->displayName = $displayName;
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
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @return string[]
     * @throws \exceptions\DatabaseException
     */
    public function getPermissionCodes(): array
    {
        return RoleDatabaseHandler::getRolePermissionCodes($this->id);
    }
}