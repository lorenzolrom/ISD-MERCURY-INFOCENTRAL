<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/13/2020
 * Time: 2:15 PM
 */


namespace extensions\trs\commands;


use commands\Command;
use controllers\CurrentUserController;
use exceptions\MercuryException;
use extensions\trs\database\CommodityCategoryDatabaseHandler;
use extensions\trs\models\CommodityCategory;
use utilities\HistoryRecorder;

class DeleteCommodityCategoryCommand implements Command
{
    public const PERMISSION = 'trs_commodities-a';

    private $result;
    private $error;

    private $cc; // CommodityCategory object

    /**
     * DeleteCommodityCategoryCommand constructor.
     * @param CommodityCategory $cc
     */
    public function __construct(CommodityCategory $cc)
    {
        $this->cc = $cc;
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

        HistoryRecorder::writeHistory('TRS_CommodityCategory', HistoryRecorder::DELETE, $this->cc->getId(), $this->cc);

        $this->result = CommodityCategoryDatabaseHandler::delete($this->cc->getId());

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