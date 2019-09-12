<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/13/2019
 * Time: 4:41 PM
 */


namespace models\tickets;


use database\tickets\TicketDatabaseHandler;
use database\tickets\UpdateDatabaseHandler;
use models\Model;
use utilities\Validator;

class Ticket extends Model
{
    public const CLOSED = 'clo';
    public const NEW = 'new';
    public const RESPONDED = 'res';
    public const REOPENED = 'reo';

    public const STATIC_STATUSES = array(
        self::NEW => 'New',
        self::CLOSED => 'Closed',
        self::RESPONDED => 'Responded',
        self::REOPENED => 'Reopened'
    );

    private const CONTACT_RULES = array(
        'null' => TRUE,
        'username' => TRUE
    );

    private const DESIRED_DATE_RULES = array(
        'null' => TRUE,
        'type' => 'date'
    );

    private const SCHEDULED_DATE_RULES = array(
        'null' => TRUE,
        'type' => 'date'
    );

    private $id;
    private $workspace;
    private $number;
    private $title;
    private $contact;
    private $type;
    private $category;
    private $status;
    private $closureCode;
    private $severity;
    private $desiredDate;
    private $scheduledDate;

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
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getTitle(): string
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
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getCategory(): int
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getClosureCode()
    {
        return $this->closureCode;
    }

    /**
     * @return int|null
     */
    public function getSeverity(): ?int
    {
        return $this->severity;
    }

    /**
     * @return string|null
     */
    public function getDesiredDate(): ?string
    {
        return $this->desiredDate;
    }

    /**
     * @return string|null
     */
    public function getScheduledDate(): ?string
    {
        return $this->scheduledDate;
    }

    /**
     * @return Update[]
     * @throws \exceptions\DatabaseException
     */
    public function getUpdates(): array
    {
        return UpdateDatabaseHandler::selectByTicket($this->id);
    }

    /**
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public function getLastUpdateTime(): ?string
    {
        return UpdateDatabaseHandler::getLastUpdateTime($this->id);
    }

    /**
     * @return Update
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public function getLastUpdate(): Update
    {
        return UpdateDatabaseHandler::selectLastByTicket($this->getId());
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public function getAssignees(): array
    {
        return TicketDatabaseHandler::selectAssignees($this->getId());
    }

    /**
     * @return string[]
     * @throws \exceptions\DatabaseException
     */
    public function getAssigneeCodes(): array
    {
        $assignees = TicketDatabaseHandler::selectAssignees($this->getId());

        $codes = array();

        foreach($assignees as $assignee)
        {
            if(strlen($assignee['user']) === 0)
                $codes[] = $assignee['team'];
            else
                $codes[] = $assignee['team'] . '-' . $assignee['user'];
        }

        return $codes;
    }

    /**
     * @return Ticket[]
     * @throws \exceptions\DatabaseException
     */
    public function getLinked(): array
    {
        return TicketDatabaseHandler::selectLinkedTickets($this->id);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateContact(?string $val): bool
    {
        return Validator::validate(self::CONTACT_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateDesiredDate(?string $val): bool
    {
        return Validator::validate(self::DESIRED_DATE_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateScheduledDate(?string $val): bool
    {
        return Validator::validate(self::SCHEDULED_DATE_RULES, $val);
    }
}