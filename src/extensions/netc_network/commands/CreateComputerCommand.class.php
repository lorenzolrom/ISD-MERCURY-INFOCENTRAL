<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 12/23/2019
 * Time: 9:57 AM
 */


namespace extensions\netc_network\commands;


use commands\Command;
use controllers\CurrentUserController;
use extensions\netc_network\models\Computer;

/**
 * Class CreateComputerCommand
 * @package extensions\netc_network\commands
 */
class CreateComputerCommand implements Command
{
    private static const PERMISSION = 'netc_computers-w';

    private $createdComputer = NULL;
    private $errors = array();

    public function __construct()
    {

    }

    /**
     * Create a computer object
     * @return bool Was the command successful?
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function execute(): bool
    {
        CurrentUserController::validatePermission(self::PERMISSION);

        // TODO: Implement execute() method.

        // Create computer object

        // Create interface objects, if defined
    }

    /**
     * @return mixed The output of a successful command, defined by the command
     */
    public function getResult():?Computer
    {
        return $this->createdComputer;
    }

    /**
     * @return string[] An array of error messages
     */
    public function getError(): array
    {
        return $this->errors;
    }
}