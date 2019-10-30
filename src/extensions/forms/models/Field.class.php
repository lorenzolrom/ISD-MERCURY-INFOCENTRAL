<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 10/18/2019
 * Time: 6:13 AM
 */


namespace extensions\forms\models;


use models\Model;

class Field extends Model
{
    private $id;
    private $form;
    private $sequence;
    private $private;
    private $type;
    private $placeholder;
    private $required;
    private $validation;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getForm(): int
    {
        return $this->form;
    }

    /**
     * @return int
     */
    public function getSequence(): int
    {
        return $this->sequence;
    }

    /**
     * @return int
     */
    public function getPrivate(): int
    {
        return $this->private;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    /**
     * @return int
     */
    public function getRequired(): int
    {
        return $this->required;
    }

    /**
     * @return int|null
     */
    public function getValidation(): ?int
    {
        return $this->validation;
    }


}