<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/24/2019
 * Time: 11:23 AM
 */


namespace models\lockshop;


use database\lockshop\CoreDatabaseHandler;
use exceptions\ValidationException;
use models\Model;
use utilities\Validator;

class Core extends Model
{
    private const CODE_RULES = array(
        'name' => 'Code',
        'lower' => 1,
        'upper' => 32,
        'alnum' => TRUE
    );

    private const QUANTITY_RULES = array(
        'name' => 'Quantity',
        'type' => 'int',
        'positive' => TRUE
    );

    private $id;
    private $system;
    private $code;
    private $quantity;

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
    public function getSystem(): int
    {
        return $this->system;
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
    public function getQuantity(): string
    {
        return $this->quantity;
    }

    /**
     * @param int $system
     * @param string|null $code
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function __validateCode(int $system, ?string $code): bool
    {
        if(CoreDatabaseHandler::selectIdByCode($system, (string)$code) !== NULL)
            throw new ValidationException('Code already in use', ValidationException::VALUE_ALREADY_TAKEN);

        return Validator::validate(self::CODE_RULES, $code);
    }
    /**
     * @param string|null $quantity
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateQuantity(?string $quantity): bool
    {
        return Validator::validate(self::QUANTITY_RULES, $quantity);
    }
}