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


use database\tickets\UpdateDatabaseHandler;

class Ticket
{
    private $id;
    private $workspace;
    private $number;
    private $title;
    private $contact;
    private $type;
    private $category;
    private $status;
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
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->status;
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
}