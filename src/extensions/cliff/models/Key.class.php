<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/02/2020
 * Time: 9:14 AM
 */


namespace extensions\cliff\models;


use models\Model;
use utilities\Validator;

class Key extends Model
{
    private const STAMP_RULES = array(
        'name' => 'Stamp',
        'lower' => 1,
        'upper' => 32,
        'alnumdsp' => TRUE
    );

    private const BITTING_RULES = array(
        'name' => 'Bitting',
        'alnum' => TRUE,
        'lower' => 1
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
    public $bitting;
    public $type;
    public $keyway;
    public $notes;

    // Soft attributes obtained through joins
    public $systemCode;
    public $systemName;

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
    public function getBitting(): string
    {
        return $this->bitting;
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
     * @return string
     */
    public function getSystemCode(): string
    {
        return $this->systemCode;
    }

    /**
     * @return string
     */
    public function getSystemName(): string
    {
        return $this->systemName;
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
    public static function validateBitting(?string $val): bool
    {
        return Validator::validate(self::BITTING_RULES, $val);
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
