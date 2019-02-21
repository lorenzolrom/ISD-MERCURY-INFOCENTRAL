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
use messages\ValidationError;

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

    /**
     * @param string $displayName
     * @return bool Was the display name updated?
     * @throws \exceptions\DatabaseException
     */
    public function setDisplayName(string $displayName): bool
    {
        return RoleDatabaseHandler::updateDisplayName($this->id, $displayName);
    }

    /**
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public function delete(): bool
    {
        return RoleDatabaseHandler::delete($this->id);
    }

    /**
     * @param string $displayName
     * @return int
     * @throws \exceptions\DatabaseException
     */
    public static function validateDisplayName(?string $displayName): int
    {
        // Is not null
        if($displayName === NULL)
            return ValidationError::VALUE_IS_NULL;

        // Between 1 and 64 characters
        if(strlen($displayName) < 1)
            return ValidationError::VALUE_IS_TOO_SHORT;
        if(strlen($displayName) > 64)
            return ValidationError::VALUE_IS_TOO_LONG;

        // Is not already in use
        if(RoleDatabaseHandler::isDisplayNameInUse($displayName))
            return ValidationError::VALUE_ALREADY_TAKEN;

        return ValidationError::VALUE_IS_OK;
    }
}