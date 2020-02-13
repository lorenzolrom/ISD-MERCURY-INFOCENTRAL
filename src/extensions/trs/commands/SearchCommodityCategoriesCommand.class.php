<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/12/2020
 * Time: 4:39 PM
 */


namespace extensions\trs\commands;


use commands\Command;
use controllers\CurrentUserController;
use exceptions\MercuryException;
use extensions\trs\database\CommodityCategoryDatabaseHandler;
use extensions\trs\models\CommodityCategory;

class SearchCommodityCategoriesCommand implements Command
{
    public const FIELDS = array('name');
    private const PERMISSION = 'trs_commodities-r';

    private $result = NULL;
    private $error = NULL;

    private $name;

    public function __construct(array $args)
    {
        $this->name = $args['name'];
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

        $this->result = CommodityCategoryDatabaseHandler::select($this->name);

        return TRUE;
    }

    /**
     * @return CommodityCategory[] The output of a successful command, defined by the command
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