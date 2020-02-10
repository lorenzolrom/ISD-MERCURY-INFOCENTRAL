<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/10/2020
 * Time: 3:51 PM
 */


namespace extensions\trs\commands;


use commands\Command;
use controllers\CurrentUserController;
use exceptions\MercuryException;
use extensions\trs\database\OrganizationDatabaseHandler;
use extensions\trs\models\Organization;
use utilities\HistoryRecorder;

class DeleteOrganizationCommand implements Command
{
    private const PERMISSION = 'trs_organizations-w';

    private $result = NULL;
    private $error = NULL;

    private $org;

    public function __construct(Organization $org)
    {
        $this->org = $org;
    }

    /**
     * Executes the instructions of the command
     * @return bool Was the command successful?
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public function execute(): bool
    {
        CurrentUserController::validatePermission(self::PERMISSION);

        // History
        HistoryRecorder::writeHistory('TRS_Organization', HistoryRecorder::DELETE, $this->org->getId(), $this->org);

        // Delete
        $this->result = OrganizationDatabaseHandler::delete($this->org->id);

        return $this->result;
    }

    /**
     * @return mixed The output of a successful command, defined by the command
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return MercuryException|null The exception thrown by execution
     */
    public function getError(): ?MercuryException
    {
        return $this->error;
    }
}