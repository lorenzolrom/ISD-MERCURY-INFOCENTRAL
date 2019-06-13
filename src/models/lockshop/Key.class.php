<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/24/2019
 * Time: 11:24 AM
 */


namespace models\lockshop;


use database\lockshop\KeyDatabaseHandler;
use exceptions\ValidationException;
use models\Model;
use utilities\Validator;

class Key extends Model
{
    private const CODE_RULES = array(
        'name' => 'Code',
        'lower' => 1,
        'upper' => 32,
        'alnum' => TRUE
    );

    private const BITTING_RULES = array(
        'name' => 'Bitting',
        'alnumds' => TRUE,
        'upper' => 32
    );

    private const KEYWAY_RULES = array(
        'name' => 'Keyway',
        'alnumds' => TRUE,
        'upper' => 32
    );

    private const QUANTITY_RULES = array(
        'name' => 'Quantity',
        'type' => 'int',
        'positive' => TRUE
    );

    private $id;
    private $system;
    private $code;
    private $bitting;
    private $keyway;
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
    public function getBitting(): string
    {
        return $this->bitting;
    }

    /**
     * @return mixed
     */
    public function getKeyway()
    {
        return $this->keyway;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
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
        if(KeyDatabaseHandler::selectIdByCode($system, (string)$code) !== NULL)
            throw new ValidationException('Code already in use', ValidationException::VALUE_ALREADY_TAKEN);

        return Validator::validate(self::CODE_RULES, $code);
    }

    /**
     * @param string|null $bitting
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateBitting(?string $bitting): bool
    {
        return Validator::validate(self::BITTING_RULES, $bitting);
    }

    /**
     * @param string|null $keyway
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateKeyway(?string $keyway): bool
    {
        return Validator::validate(self::KEYWAY_RULES, $keyway);
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