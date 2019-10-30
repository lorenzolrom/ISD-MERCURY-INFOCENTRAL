<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 10/18/2019
 * Time: 6:11 AM
 */


namespace extensions\forms\models;


use models\Model;

class Form extends Model
{
    private $id;
    private $title;
    private $owner;
    private $active;
    private $emailRequired;
    private $sendConfirmationEmail;

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
     * @return int
     */
    public function getOwner(): int
    {
        return $this->owner;
    }

    /**
     * @return int
     */
    public function getActive(): int
    {
        return $this->active;
    }

    /**
     * @return int
     */
    public function getEmailRequired(): int
    {
        return $this->emailRequired;
    }

    /**
     * @return int
     */
    public function getSendConfirmationEmail(): int
    {
        return $this->sendConfirmationEmail;
    }


}