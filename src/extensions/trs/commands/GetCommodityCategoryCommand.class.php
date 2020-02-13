<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/12/2020
 * Time: 4:32 PM
 */


namespace extensions\trs\commands;


use commands\Command;
use controllers\CurrentUserController;
use exceptions\EntryNotFoundException;
use exceptions\MercuryException;
use extensions\trs\database\CommodityCategoryDatabaseHandler;
use extensions\trs\models\CommodityCategory;

class GetCommodityCategoryCommand implements Command
{
    private const PERMISSION = 'trs_commodities-r';

    private $result = NULL;
    private $error = NULL;

    private $id = NULL;

    public function __construct(int $id)
    {
        $this->id = $id;
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

        try
        {
            $this->result = CommodityCategoryDatabaseHandler::selectById($this->id);
            return TRUE;
        }
        catch(EntryNotFoundException $e)
        {
            $this->error = $e;
            return FALSE;
        }
    }

    /**
     * @return CommodityCategory The output of a successful command, defined by the command
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