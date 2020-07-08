<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 4/02/2020
 * Time: 9:14 AM
 */


namespace extensions\cliff\models;


use models\Model;
use utilities\Validator;

class Core extends Model
{
    private const STAMP_RULES = array(
        'name' => 'Stamp',
        'lower' => 1,
        'upper' => 32,
        'alnumdsp' => TRUE
    );

    private const PINDATA_RULES = array(
        'name' => 'Pin Data',
        'null' => FALSE
    );

    private const TYPE_RULES = array(
        'name' => 'Type',
        'lower' => 1
    );

    private const KEYWAY_RULES = array(
        'name' => 'Keyway',
        'lower' => 1
    );

    private const NOTES_RULES = array(
        'name' => 'Notes',
        'lower' => 0
    );

    public $id;
    public $system;
    public $stamp;
    public $pinData;
    public $type;
    public $keyway;
    public $notes;

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
    public function getStamp(): string
    {
        return $this->stamp;
    }

    /**
     * @return string
     */
    public function getPinData(): string
    {
        return $this->pinData;
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
    public function getKeyway(): string
    {
        return $this->keyway;
    }

    /**
     * @return string
     */
    public function getNotes(): string
    {
        return $this->notes;
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateStamp(?string $val): bool
    {
        return Validator::validate(self::STAMP_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validatePinData(?string $val): bool
    {
        return Validator::validate(self::PINDATA_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateType(?string $val): bool
    {
        return Validator::validate(self::TYPE_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateKeyway(?string $val): bool
    {
        return Validator::validate(self::KEYWAY_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateNotes(?string $val): bool
    {
        return Validator::validate(self::NOTES_RULES, $val);
    }
}
