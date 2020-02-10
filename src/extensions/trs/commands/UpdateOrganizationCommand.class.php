<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/10/2020
 * Time: 4:02 PM
 */


namespace extensions\trs\commands;


use commands\Command;
use controllers\CurrentUserController;
use exceptions\MercuryException;
use exceptions\ValidationError;
use extensions\trs\database\OrganizationDatabaseHandler;
use extensions\trs\models\Organization;
use utilities\HistoryRecorder;
use utilities\Validator;

class UpdateOrganizationCommand implements Command
{
    private const PERMISSION = 'trs_organizations-w';

    private $result = NULL;
    private $error = NULL;

    private $org = NULL;
    private $args = NULL;

    public function __construct(Organization $org, array $args)
    {
        $this->org = $org;
        $this->args = $args;
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

        // Validator
        try
        {
            Validator::validateClass('extensions\trs\models\Organization', Organization::FIELDS, $this->args);
        }
        catch(ValidationError $e)
        {
            $this->error = $e;
            return FALSE;
        }

        // History
        HistoryRecorder::writeHistory('TRS_Organization', HistoryRecorder::MODIFY, $this->org->getId(), $this->org, $this->args);

        // Update
        OrganizationDatabaseHandler::update($this->org->getId(), $this->args);

        return TRUE;
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