<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 10:44 PM
 */


namespace models;


use utilities\Validator;

class Notification extends Model
{
    private const TITLE_RULES = array(
        'name' => 'Title',
        'lower' => 1
    );

    private const DATA_RULES = array(
        'name' => 'Message',
        'lower' => 1
    );

    private const IMPORTANT_RULES = array(
        'name' => 'Important',
        'acceptable' => array(0,1)
    );

    private $id;
    private $user;
    private $title;
    private $data;
    private $read;
    private $deleted;
    private $important;
    private $time;

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
    public function getUser(): int
    {
        return $this->user;
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
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getRead(): int
    {
        return $this->read;
    }

    /**
     * @return int
     */
    public function getDeleted(): int
    {
        return $this->deleted;
    }

    /**
     * @return int
     */
    public function getImportant(): int
    {
        return $this->important;
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->time;
    }

    /**
     * @param string|null $title
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateTitle(?string $title): bool
    {
        return Validator::validate(self::TITLE_RULES, $title);
    }

    /**
     * @param string|null $data
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateData(?string $data): bool
    {
        return Validator::validate(self::DATA_RULES, $data);
    }

    /**
     * @param string|null $important
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateImportant(?string $important): bool
    {
        return Validator::validate(self::IMPORTANT_RULES, $important);
    }
}