<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/03/2019
 * Time: 10:19 AM
 */


namespace extensions\facilities\models;


use models\Model;
use utilities\Validator;

class Floorplan extends Model
{
    private const FLOOR_RULES = array(
        'name' => 'Floor',
        'lower' => 1,
        'upper' => 16,
        'alnumds' => TRUE
    );

    public const IMAGE_TYPE_RULES = array( // This is public so FloorplanOperator can access it
        'name' => 'Image type',
        'acceptable' => array('image/jpeg', 'image/png', 'image/svg', 'image/svg+xml')

    );

    private $id;
    private $building;
    private $floor;
    private $imageType;
    private $imageName;

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
    public function getBuilding(): int
    {
        return $this->building;
    }

    /**
     * @return string
     */
    public function getFloor(): string
    {
        return $this->floor;
    }

    /**
     * @return string
     */
    public function getImageType(): string
    {
        return $this->imageType;
    }

    /**
     * @return string
     */
    public function getImageName(): string
    {
        return $this->imageName;
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateFloor(?string $val): bool
    {
        return Validator::validate(self::FLOOR_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateImageType(?string $val): bool
    {
        return Validator::validate(self::IMAGE_TYPE_RULES, $val);
    }
}