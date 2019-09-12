<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 9/12/2019
 * Time: 5:40 PM
 */


namespace models\tickets;


use models\Model;

class Search extends Model
{
    private $id;
    private $workspace;
    private $user;
    private $name;
    private $number;
    private $title;
    private $contact;
    private $assignees;
    private $severity;
    private $type;
    private $category;
    private $status;
    private $closureCode;
    private $desiredDateStart;
    private $desiredDateEnd;
    private $scheduledDateStart;
    private $scheduledDateEnd;
    private $description;

    /**
     * Search constructor.
     * @param $id
     * @param $workspace
     * @param $user
     * @param $name
     * @param $number
     * @param $title
     * @param $contact
     * @param $assignees
     * @param $severity
     * @param $type
     * @param $category
     * @param $status
     * @param $closureCode
     * @param $desiredDateStart
     * @param $desiredDateEnd
     * @param $scheduledDateStart
     * @param $scheduledDateEnd
     * @param $description
     */
    public function __construct(int $id, int $workspace, int $user, string $name, ?string $number, ?string $title, ?string $contact, ?string $assignees, ?string $severity, ?string $type, ?string $category, ?string $status, ?string $closureCode, ?string $desiredDateStart, ?string $desiredDateEnd, ?string $scheduledDateStart, ?string $scheduledDateEnd, ?string $description)
    {
        $this->id = $id;
        $this->workspace = $workspace;
        $this->user = $user;
        $this->name = $name;
        $this->number = $number;
        $this->title = $title;
        $this->contact = $contact;
        $this->assignees = $assignees;
        $this->severity = $severity;
        $this->type = $type;
        $this->category = $category;
        $this->status = $status;
        $this->closureCode = $closureCode;
        $this->desiredDateStart = $desiredDateStart;
        $this->desiredDateEnd = $desiredDateEnd;
        $this->scheduledDateStart = $scheduledDateStart;
        $this->scheduledDateEnd = $scheduledDateEnd;
        $this->description = $description;
    }

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
    public function getWorkspace(): int
    {
        return $this->workspace;
    }

    /**
     * @return int
     */
    public function getUser(): int
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getContact(): ?string
    {
        return $this->contact;
    }

    /**
     * @return string|null
     */
    public function getAssignees(): ?string
    {
        return $this->assignees;
    }

    /**
     * @return string|null
     */
    public function getSeverity(): ?string
    {
        return $this->severity;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getClosureCode(): ?string
    {
        return $this->closureCode;
    }

    /**
     * @return string|null
     */
    public function getDesiredDateStart(): ?string
    {
        return $this->desiredDateStart;
    }

    /**
     * @return string|null
     */
    public function getDesiredDateEnd(): ?string
    {
        return $this->desiredDateEnd;
    }

    /**
     * @return string|null
     */
    public function getScheduledDateStart(): ?string
    {
        return $this->scheduledDateStart;
    }

    /**
     * @return string|null
     */
    public function getScheduledDateEnd(): ?string
    {
        return $this->scheduledDateEnd;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }




}