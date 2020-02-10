<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/10/2020
 * Time: 3:13 PM
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

class CreateOrganizationCommand implements Command
{
    private const PERMISSION = 'trs_organizations-w';

    private $args = array();

    private $result = NULL;
    private $error = NULL;

    public function __construct(array $args)
    {
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

        // Validate fields against Organization class
        try
        {
            Validator::validateClass('extensions\trs\models\Organization', Organization::FIELDS, $this->args);
        }
        catch(ValidationError $e)
        {
            $this->error = $e;
            return FALSE;
        }

        // Create Organization
        $org = OrganizationDatabaseHandler::insert($this->args);

        // Create History record
        HistoryRecorder::writeHistory('TRS_Organization', HistoryRecorder::CREATE, $org->getId(), $org);

        $this->result = $org;

        return TRUE;
    }

    /**
     * @return Organization The output of a successful command, defined by the command
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