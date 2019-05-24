<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/24/2019
 * Time: 11:26 AM
 */


namespace models\lockshop;


use database\lockshop\CoreDatabaseHandler;
use database\lockshop\KeyDatabaseHandler;
use database\lockshop\SystemDatabaseHandler;
use exceptions\ValidationException;
use utilities\Validator;

class System
{
    private const NAME_RULES = array(
        'name' => 'Name',
        'lower' => 1,
        'alnumds' => TRUE
    );

    private const CODE_RULES = array(
        'name' => 'Code',
        'lower' => 1,
        'upper' => 32,
        'alnum' => TRUE
    );

    private $id;
    private $parent;
    private $name;
    private $code;
    private $master;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getParent(): ?int
    {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return int|null
     */
    public function getMaster(): ?int
    {
        return $this->master;
    }

    /**
     * @return Core[]
     * @throws \exceptions\DatabaseException
     */
    public function getCores(): array
    {
        return CoreDatabaseHandler::selectBySystem($this->id);
    }

    /**
     * @return Key[]
     * @throws \exceptions\DatabaseException
     */
    public function getKeys(): array
    {
        return KeyDatabaseHandler::selectBySystem($this->id);
    }

    /**
     * @param string|null $name
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateName(?string $name): bool
    {
        return Validator::validate(self::NAME_RULES, $name);
    }

    /**
     * @param string|null $code
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function _validateCode(?string $code): bool
    {
        // Code is unique
        if(SystemDatabaseHandler::selectIdByCode((string)$code) !== NULL)
            throw new ValidationException('Code already in use', ValidationException::VALUE_ALREADY_TAKEN);

        return Validator::validate(self::CODE_RULES, $code);
    }
}