<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/08/2019
 * Time: 10:56 AM
 */


namespace extensions\faclocks\models;


use models\Model;

class System extends Model
{
    private $id;
    private $code;
    private $master;
    private $control;
    private $parent;

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
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return int|null
     */
    public function getMaster(): ?int
    {
        return $this->master;
    }

    /**
     * @return int|null
     */
    public function getControl(): ?int
    {
        return $this->control;
    }

    /**
     * @return int|null
     */
    public function getParent(): ?int
    {
        return $this->parent;
    }


}