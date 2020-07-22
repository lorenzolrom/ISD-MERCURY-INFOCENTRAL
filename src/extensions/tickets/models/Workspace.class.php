<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/13/2019
 * Time: 4:40 PM
 */


namespace extensions\tickets\models;


use business\SecretOperator;
use exceptions\EntryNotFoundException;
use extensions\tickets\database\TeamDatabaseHandler;
use extensions\tickets\database\WorkspaceDatabaseHandler;
use exceptions\ValidationException;
use models\Model;
use models\Secret;
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
     * @return Secret[]
     * @throws \exceptions\DatabaseException
     */
    public function getAllowedSecrets(): array
    {
        $secrets = array();

        try
        {
            foreach(WorkspaceDatabaseHandler::getAllowedSecrets($this->id) as $id)
            {
                $secrets[] = SecretOperator::getSecretById($id);
            }
        }
        catch(EntryNotFoundException $e){} // Ignore invalid Secret ID, it should not happen

        return $secrets;
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
