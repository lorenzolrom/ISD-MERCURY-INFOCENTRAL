<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/13/2019
 * Time: 4:43 PM
 */


namespace extensions\tickets\models;


use extensions\tickets\database\AttributeDatabaseHandler;
use exceptions\ValidationException;
use models\Model;
use utilities\Validator;

class Attribute extends Model
{
    public const TYPES = array('status', 'category', 'severity', 'type', 'closureCode');
    public const CLOSED_STATUS = 'clo';
    public const NEW_STATUS = 'new';

    public const TYPE_RULES = array(
        'name' => 'Type',
        'acceptable' => self::TYPES
    );

    public const CODE_RULES = array(
        'name' => 'Code',
        'exact' => 4,
        'alnum' => TRUE
    );

    public const NAME_RULES = array(
        'name' => 'Name',
        'lower' => 1,
        'alnumdss' => TRUE
    );

    private $id;
    private $workspace;
    private $type;
    private $code;
    private $name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getWorkspace(): int
    {
        return $this->workspace;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string|null $type
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function _validateType(?string $type): bool
    {
        return Validator::validate(self::TYPE_RULES, $type);
    }

    /**
     * @param int $workspace
     * @param string|null $type
     * @param string|null $code
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function __validateCode(int $workspace, ?string $type, ?string $code): bool
    {
        // Code is unique
        if(AttributeDatabaseHandler::selectIdByCode($workspace, (string)$type, (string)$code) !== NULL)
            throw new ValidationException('Code already in use', ValidationException::VALUE_ALREADY_TAKEN);

        return Validator::validate(self::CODE_RULES, $code);
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
}
