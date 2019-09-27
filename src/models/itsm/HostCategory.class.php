<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/05/2019
 * Time: 7:19 PM
 */


namespace models\itsm;

use exceptions\ValidationException;
use database\itsm\HostCategoryDatabaseHandler;
use models\Model;
use utilities\Validator;

class HostCategory extends Model
{
    private const NAME_RULES = array(
        'name' => 'Name',
        'lower' => 1,
        'upper' => 64
    );

    private const DISPLAYED_RULES = array(
        'name' => 'Displayed',
        'allowed' => array(0,1)
    );

    private $id;
    private $name;
    private $displayed;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getDisplayed(): int
    {
        return $this->displayed;
    }

    /**
     * @return Host[]
     * @throws \exceptions\DatabaseException
     */
    public function getHosts(): array
    {
        return HostCategoryDatabaseHandler::getHosts($this->id);
    }

    /**
     * @param string|null $name
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function _validateName(?string $name): bool
    {
        Validator::validate(self::NAME_RULES, $name);

        if(HostCategoryDatabaseHandler::selectIdByName($name) !== NULL)
            throw new ValidationException('Name already in use', ValidationException::VALUE_ALREADY_TAKEN);

        return TRUE;
    }

    /**
     * @param string|null $displayed
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateDisplayed(?string $displayed): bool
    {
        return Validator::validate(self::DISPLAYED_RULES, $displayed);
    }
}