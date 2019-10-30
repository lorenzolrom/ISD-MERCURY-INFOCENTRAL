<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 10/18/2019
 * Time: 6:12 AM
 */


namespace extensions\forms\models;


use models\Model;

class Validation extends Model
{
    private $id;
    private $title;
    private $regex;
    private $errorMessage;

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
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getRegex(): string
    {
        return $this->regex;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }


}