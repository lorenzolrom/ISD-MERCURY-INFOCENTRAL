<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 11/04/2019
 * Time: 10:30 AM
 */


namespace extensions\facilities\models;


use extensions\facilities\database\SpacePointDatabaseHandler;
use models\Model;
use utilities\Validator;

/**
 * The representation of a Location on a Floorplan
 * Class Space
 * @package extensions\facilities\models
 */
class Space extends Model
{
    private const HEX_COLOR_RULES = array(
        'name' => 'Color',
        'exact' => 6,
        'alnum' => TRUE
    );

    private const AREA_RULES = array(
        'name' => 'Area',
        'type' => 'float',
        'positive' => TRUE
    );

    private const UNIT_RULES = array(
        'name' => 'Unit',
        'lower' => 0,
        'upper' => 2
    );

    private $location; // This is the primary key AND a reference to a location
    private $floor; // This is the floor the space is located on
    private $hexColor; // User-defined region color
    private $area; // User-defined area of as-built location, decimal(6,1)
    private $unit; // User-defined units for area, 0-2 characters

    /**
     * @return int
     */
    public function getLocation(): int
    {
        return $this->location;
    }

    public function getFloor(): int
    {
        return $this->floor;
    }

    /**
     * @return string
     */
    public function getHexColor(): string
    {
        return $this->hexColor;
    }

    /**
     * @return float
     */
    public function getArea(): float
    {
        return $this->area;
    }

    /**
     * @return string
     */
    public function getUnit(): string
    {
        return $this->unit;
    } // User-defined units

    /**
     * @return SpacePoint[]
     * @throws \exceptions\DatabaseException
     */
    public function getPoints(): array
    {
        return SpacePointDatabaseHandler::selectBySpace($this->location);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateHexColor(?string $val): bool
    {
        return Validator::validate(self::HEX_COLOR_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateArea(?string $val): bool
    {
        return Validator::validate(self::AREA_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateUnit(?string $val): bool
    {
        return Validator::validate(self::UNIT_RULES, $val);
    }
}