<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/02/2020
 * Time: 9:13 AM
 */


namespace extensions\cliff\models;


use models\Model;
use utilities\Validator;

class System extends Model
{
    private const CODE_RULES = array(
        'name' => 'Code',
        'lower' => 1,
        'upper' => 16,
        'alnum' => TRUE
    );

    private const NAME_RULES = array (
        'name' => 'Name',
        'lower' => 1,
        'alnumds' => TRUE
    );

    public $id;
    public $code;
    public $name;

    public function getId(): int
    {
        return $this->id;
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
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateCode(?string $val): bool
    {
        return Validator::validate(self::CODE_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateName(?string $val): bool
    {
        return Validator::validate(self::NAME_RULES, $val);
    }
}
