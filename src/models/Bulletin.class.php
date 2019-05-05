<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/28/2019
 * Time: 12:16 PM
 */


namespace models;


use utilities\Validator;

class Bulletin extends Model
{
    private const START_DATE_RULES = array(
        'name' => 'Start Date',
        'type' => 'date'
    );

    private const END_DATE_RULES = array(
        'name' => 'End Date',
        'null' => TRUE,
        'type' => 'date'
    );

    private const TITLE_RULES = array(
        'name' => 'Title',
        'lower' => 1
    );

    private const MESSAGE_RULES = array(
        'name' => 'Message',
        'lower' => 1
    );

    private const INACTIVE_RULES = array(
        'name' => 'Disabled',
        'acceptable' => array(0, 1)
    );

    private const TYPE_RULES = array(
        'name' => 'Type',
        'acceptable' => array('a', 'i')
    );

    private $id;
    private $startDate;
    private $endDate;
    private $title;
    private $message;
    private $inactive;
    private $type;

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
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * @return string
     */
    public function getEndDate(): string
    {
        return $this->endDate;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getInactive(): int
    {
        return $this->inactive;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateStartDate(?string $val): bool
    {
        return Validator::validate(self::START_DATE_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateEndDate(?string $val): bool
    {
        return Validator::validate(self::END_DATE_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateTitle(?string $val): bool
    {
        return Validator::validate(self::TITLE_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateMessage(?string $val): bool
    {
        return Validator::validate(self::MESSAGE_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateInactive(?string $val): bool
    {
        return Validator::validate(self::INACTIVE_RULES, $val);
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
}