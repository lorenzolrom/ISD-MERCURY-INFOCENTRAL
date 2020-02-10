<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/08/2020
 * Time: 2:34 PM
 */


namespace extensions\trs\models;


use models\Model;
use utilities\Validator;

class Organization extends Model
{
    // Database fields
    public const FIELDS = array(
        'name',
        'type',
        'phone',
        'email',
        'street',
        'city',
        'state',
        'zip',
        'approved'
    );

    // Valid Org types
    public const TYPES = array('partner', 'donor');

    private const NAME_RULES = array(
        'name' => 'Name',
        'lower' => 1
    );

    private const TYPE_RULES = array(
        'name' => 'Type',
        'acceptable' => self::TYPES
    );

    private const PHONE_RULES = array(
        'name' => 'Phone #',
        'lower' => 1,
        'upper' => 14
    );

    private const EMAIL_RULES = array(
        'name' => 'E-Mail Address',
        'null' => FALSE
    );

    private const STREET_RULES = array(
        'name' => 'Street Address',
        'lower' => 1
    );

    private const CITY_RULES = array(
        'name' => 'City',
        'lower' => 1
    );

    private const STATE_RULES = array(
        'name' => 'State',
        'exact' => 2
    );

    private const ZIP_RULES = array(
        'name' => 'Zip Code',
        'lower' => 5,
        'upper' => 10
    );

    private const APPROVED_RULES = array(
        'name' => 'Approved',
        'acceptable' => array('0', '1')
    );

    public $id;
    public $name;
    public $type;
    public $phone;
    public $email;
    public $street;
    public $city;
    public $state;
    public $zip;
    public $approved;

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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * @return int
     */
    public function getApproved(): int
    {
        return $this->approved;
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
     * @param string|null $type
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateType(?string $type): bool
    {
        return Validator::validate(self::TYPE_RULES, $type);
    }

    /**
     * @param string|null $phone
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validatePhone(?string $phone): bool
    {
        return Validator::validate(self::PHONE_RULES, $phone);
    }

    /**
     * @param string|null $email
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateEmail(?string $email): bool
    {
        return Validator::validate(self::EMAIL_RULES, $email);
    }

    /**
     * @param string|null $street
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateStreet(?string $street): bool
    {
        return Validator::validate(self::STREET_RULES, $street);
    }

    /**
     * @param string|null $city
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateCity(?string $city): bool
    {
        return Validator::validate(self::CITY_RULES, $city);
    }

    /**
     * @param string|null $state
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateState(?string $state): bool
    {
        return Validator::validate(self::STATE_RULES, $state);
    }

    /**
     * @param string|null $zip
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateZip(?string $zip): bool
    {
        return Validator::validate(self::ZIP_RULES, $zip);
    }

    /**
     * @param string|null $approved
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateApproved(?string $approved): bool
    {
        return Validator::validate(self::APPROVED_RULES, $approved);
    }
}