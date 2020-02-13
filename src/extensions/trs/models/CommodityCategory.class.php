<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/12/2020
 * Time: 4:07 PM
 */


namespace extensions\trs\models;


use models\Model;
use utilities\Validator;

class CommodityCategory extends Model
{
    public const FIELDS = array('name');

    private const NAME_RULES = array(
        'name' => 'Name',
        'lower' => 1,
        'upper' => 32
    );

    public $id;
    public $name;

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