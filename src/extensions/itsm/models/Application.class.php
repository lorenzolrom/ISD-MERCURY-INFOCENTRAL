<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 10:24 PM
 */


namespace extensions\itsm\models;


use models\Model;
use utilities\Validator;

class Application extends Model
{
    private const NAME_RULES = array(
        'name' => 'Name',
        'lower' => 1,
        'upper' => 64
    );

    private const DESCRIPTION_RULES = array(
        'name' => 'Description',
        'lower' => 1
    );

    private const OWNER_RULES = array(
        'name' => 'Owner',
        'username' => true
    );

    private const TYPE_RULES = array(
        'name' => 'Type',
        'attribute' => true,
        'attrExtension' => 'itsm',
        'attrType' => 'aitt'
    );

    private const STATUS_RULES = array(
        'name' => 'Status',
        'attribute' => true,
        'attrExtension' => 'itsm',
        'attrType' => 'aits'
    );

    private const PUBLIC_RULES = array(
        'name' => 'Public Facing',
        'acceptable' => array(0, 1)
    );

    private const LIFE_RULES = array(
        'name' => 'Life Expectancy',
        'attribute' => true,
        'attrExtension' => 'itsm',
        'attrType' => 'aitl'
    );

    private const DATA_RULES = array(
        'name' => 'Data Volume',
        'attribute' => true,
        'attrExtension' => 'itsm',
        'attrType' => 'aitd'
    );

    private const AUTH_RULES = array(
        'name' => 'Auth Type',
        'attribute' => true,
        'attrExtension' => 'itsm',
        'attrType' => 'aita'
    );

    private const PORT_RULES = array(
        'name' => 'Port',
        'upper' => 5
    );

    private $id;
    private $number;
    private $name;
    private $description;
    private $owner;
    private $type;
    private $status;
    private $publicFacing;
    private $lifeExpectancy;
    private $dataVolume;
    private $authType;
    private $port;

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
    public function getNumber(): int
    {
        return $this->number;
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
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getOwner(): int
    {
        return $this->owner;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getPublicFacing(): int
    {
        return $this->publicFacing;
    }

    /**
     * @return int
     */
    public function getLifeExpectancy(): int
    {
        return $this->lifeExpectancy;
    }

    /**
     * @return int
     */
    public function getDataVolume(): int
    {
        return $this->dataVolume;
    }

    /**
     * @return int
     */
    public function getAuthType(): int
    {
        return $this->authType;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
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

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateDescription(?string $val): bool
    {
        return Validator::validate(self::DESCRIPTION_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateOwner(?string $val): bool
    {
        return Validator::validate(self::OWNER_RULES, $val);
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
    public static function validateStatus(?string $val): bool
    {
        return Validator::validate(self::STATUS_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validatePublicFacing(?string $val): bool
    {
        return Validator::validate(self::PUBLIC_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateLifeExpectancy(?string $val): bool
    {
        return Validator::validate(self::LIFE_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateDataVolume(?string $val): bool
    {
        return Validator::validate(self::DATA_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateAuthType(?string $val): bool
    {
        return Validator::validate(self::AUTH_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validatePort(?string $val): bool
    {
        return Validator::validate(self::PORT_RULES, $val);
    }
}
