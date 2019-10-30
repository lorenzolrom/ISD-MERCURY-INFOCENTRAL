<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/13/2019
 * Time: 4:40 PM
 */


namespace extensions\tickets\models;


use extensions\tickets\database\TeamDatabaseHandler;
use extensions\tickets\database\WorkspaceDatabaseHandler;
use exceptions\ValidationException;
use models\Model;
use utilities\Validator;

class Workspace extends Model
{
    private const NAME_RULES = array(
        'name' => 'Name',
        'lower' => 1,
        'upper' => 64
    );

    private $id;
    private $name;
    private $requestPortal;

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
     * @return int
     */
    public function getRequestPortal(): int
    {
        return $this->requestPortal;
    }



    /**
     * @return Team[]
     * @throws \exceptions\DatabaseException
     */
    public function getTeams(): array
    {
        return TeamDatabaseHandler::selectByWorkspace($this->id);
    }

    /**
     * @param string|null $name
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateName(?string $name): bool
    {
        // Name is unique
        if(WorkspaceDatabaseHandler::nameInUse((string)$name))
            throw new ValidationException('Name already in use', ValidationException::VALUE_ALREADY_TAKEN);

        return Validator::validate(self::NAME_RULES, $name);
    }
}