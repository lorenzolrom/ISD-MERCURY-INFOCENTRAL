<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 4/26/2019
 * Time: 10:33 AM
 */


namespace models;


use database\HistoryDatabaseHandler;

class History extends Model
{
    private $id;
    private $action;
    private $table;
    private $index;
    private $username;
    private $time;

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
    public function getAction(): string
    {
        return $this->action;
    }



    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->time;
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public function getItems(): array
    {
        return HistoryDatabaseHandler::selectHistoryItemsByHistory($this->getId());
    }
}
