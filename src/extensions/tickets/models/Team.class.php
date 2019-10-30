<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/13/2019
 * Time: 4:38 PM
 */


namespace extensions\tickets\models;


use extensions\tickets\database\TeamDatabaseHandler;
use exceptions\ValidationException;
use models\Model;
use models\User;
use utilities\Validator;

class Team extends Model
{
    private const NAME_RULES = array(
        'name' => 'Name',
        'lower' => 1,
        'upper' => 64
    );

    private $id;
    private $name;

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
     * @return User[]
     * @throws \exceptions\DatabaseException
     */
    public function getUsers(): array
    {
        return TeamDatabaseHandler::selectUsers($this->id);
    }

    /**
     * @param string|null $name
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateName(?string $name): bool
    {
        // Name is unique
        if(TeamDatabaseHandler::nameInUse((string)$name))
            throw new ValidationException('Name already in use', ValidationException::VALUE_ALREADY_TAKEN);

        return Validator::validate(self::NAME_RULES, $name);
    }
}