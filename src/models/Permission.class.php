<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * MERCURY InfoCentral
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 5:29 PM
 */


namespace models;


class Permission extends Model
{
    private $code;
    private $displayName;
    private $description;

    /**
     * Permission constructor.
     * @param string $code
     * @param string $displayName
     * @param string $description
     */
    public function __construct(string $code, string $displayName, string $description)
    {
        $this->code = $code;
        $this->displayName = $displayName;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }


}