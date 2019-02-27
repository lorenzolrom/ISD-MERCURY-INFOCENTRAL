<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * MERCURY InfoCentral
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 10:56 AM
 */


namespace models;


class Route extends Model
{
    private $id;
    private $path;
    private $extension;
    private $controller;

    /**
     * Route constructor.
     * @param int $id
     * @param string $path
     * @param string|null $extension
     * @param string $controller
     */
    public function __construct(int $id, string $path, ?string $extension, string $controller)
    {
        $this->id = $id;
        $this->path = $path;
        $this->extension = $extension;
        $this->controller = $controller;
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
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string|null
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }


}