<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/11/2020
 * Time: 10:25 AM
 */


namespace extensions\trs\commands;


use business\UserOperator;
use commands\Command;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use exceptions\MercuryException;
use extensions\trs\database\OrgRepresentativeDatabaseHandler;
use extensions\trs\models\Organization;
use models\User;

class GetRepresentativesCommand implements Command
{
    private const PERMISSION = 'trs_organizations-r';

    private $result = NULL;
    private $error = NULL;

    private $org = NULL;

    public function __construct(Organization $org)
    {
        $this->org = $org;
    }

    /**
     * Executes the instructions of the command
     * @return bool Was the command successful?
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function execute(): bool
    {
        CurrentUserController::validatePermission(self::PERMISSION);

        // Get all numerical user IDs
        $userIds = OrgRepresentativeDatabaseHandler::select($this->org->getId());
        $users = array();

        // Convert user IDs to user
        foreach($userIds as $userId)
        {
            try
            {
                $users[] = UserOperator::getUser($userId);
            }
            catch(EntryNotFoundException $e){} // Ignore, this will not occur
        }

        $this->result = $users;
        return TRUE;
    }

    /**
     * @return User[] The output of a successful command, defined by the command
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