<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/13/2020
 * Time: 2:02 PM
 */


namespace extensions\trs\commands;


use commands\Command;
use controllers\CurrentUserController;
use exceptions\MercuryException;
use exceptions\ValidationError;
use extensions\trs\database\CommodityCategoryDatabaseHandler;
use extensions\trs\models\CommodityCategory;
use utilities\HistoryRecorder;
use utilities\Validator;

class CreateCommodityCategoryCommand implements Command
{
    private const PERMISSION = 'trs_commodities-a';

    private $result = NULL;
    private $error = NULL;

    private $args = array();

    /**
     * CreateCommodityCategoryCommand constructor.
     * @param array $args
     */
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

        try
        {
            Validator::validateClass('extensions\trs\models\CommodityCategory', CommodityCategory::FIELDS, $this->args);
        }
        catch(ValidationError $e)
        {
            $this->error = $e;
            return FALSE;
        }

        $cc = CommodityCategoryDatabaseHandler::insert($this->args['name']);

        HistoryRecorder::writeHistory('TRS_CommodityCategory', HistoryRecorder::CREATE, $cc->getId(), $cc);

        $this->result = $cc;

        return TRUE;
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