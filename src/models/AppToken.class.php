<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 11:14 AM
 */


namespace models;


use database\AppTokenDatabaseHandler;

class AppToken
{
    private $id;
    private $token;
    private $name;

    /**
     * AppToken constructor.
     * @param int $id
     * @param string $token
     * @param string $name
     */
    public function __construct(int $id, string $token, string $name)
    {
        $this->id = $id;
        $this->token = $token;
        $this->name = $name;
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
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param Route $route
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public function hasAccessToRoute(Route $route): bool
    {
        return AppTokenDatabaseHandler::doesTokenHaveAccessToRoute($this->id, $route->getId());
    }
}