<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 10/18/2019
 * Time: 6:16 AM
 */


namespace models\forms;


use models\Model;

class Submission extends Model
{
    private $id;
    private $form;
    private $number;
    private $email;

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
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }



    /**
     * @return array List of submitted fields for this submission
     * {'fieldTitle'=>'submittedValue',...}
     */
    public function getSubmissionFields(): array
    {
        // TODO: implement method stub
        return array();
    }
}