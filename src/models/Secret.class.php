<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * MERCURY InfoCentral
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 11:14 AM
 */


namespace models;


use database\SecretDatabaseHandler;

class Secret extends Model
{
    private $id;
    private $secret;
    private $name;
    private $exempt;

    /**
     * AppToken constructor.
     * @param int $id
     * @param string $secret
     * @param string $name
     * @param int $exempt
     */
    public function __construct(int $id, string $secret, string $name, int $exempt)
    {
        $this->id = $id;
        $this->secret = $secret;
        $this->name = $name;
        $this->exempt = $exempt;
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
     * @return int
     */
    public function getExempt(): int
    {
        return $this->exempt;
    }



    /**
     * @param Route $route
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public function hasAccessToRoute(Route $route): bool
    {
        return SecretDatabaseHandler::doesSecretHaveAccessToRoute($this->id, $route->getId());
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public function getPermissionCodes(): array
    {
        return SecretDatabaseHandler::getSecretPermissionCodes($this->id);
    }
}