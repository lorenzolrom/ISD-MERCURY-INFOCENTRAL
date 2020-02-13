<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/11/2020
 * Time: 11:02 AM
 */


namespace extensions\trs\commands;


use business\UserOperator;
use commands\Command;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use exceptions\MercuryException;
use exceptions\ValidationError;
use extensions\trs\database\OrgRepresentativeDatabaseHandler;
use extensions\trs\models\Organization;
use utilities\HistoryRecorder;

class RemoveRepresentativeCommand implements Command
{
    private const PERMISSION = 'trs_organizations-w';

    private $result = NULL;
    private $error = NULL;

    private $org = NULL;
    private $username = NULL;

    public function __construct(Organization $org, string $username)
    {
        $this->org = $org;
        $this->username = $username;
    }

    /**
     * Executes the instructions of the command
     * @return bool Was the command successful?
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function execute(): bool
    {
        CurrentUserController::validatePermission(self::PERMISSION);

        try
        {
            // Get user (validating username provided)
            $user = UserOperator::getUserByUsername($this->username);

            // Check if representative already assigned
            if(!in_array($user->getId(), OrgRepresentativeDatabaseHandler::select($this->org->getId())))
                throw new ValidationError(array('User is not assigned'));

            // History record
            $h = HistoryRecorder::writeHistory('TRS_Organization', HistoryRecorder::MODIFY, $this->org->getId(), $this->org);
            HistoryRecorder::writeAssocHistory($h, array('removeRepresentative' => array($user->getId())));

            $this->result = OrgRepresentativeDatabaseHandler::delete($this->org->getId(), $user->getId());
            return $this->result;
        }
        catch(EntryNotFoundException $e)
        {
            $this->error = new ValidationError(array('Username is not valid'));
            return false;
        }
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