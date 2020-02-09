<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 2/08/2020
 * Time: 2:53 PM
 */


namespace extensions\trs\commands;


use commands\Command;
use controllers\CurrentUserController;
use exceptions\MercuryException;
use extensions\trs\database\OrganizationDatabaseHandler;
use extensions\trs\models\Organization;

class SearchOrganizationsCommand implements Command
{
    public const PARAMS = array(
        'name', 'type', 'phone', 'email', 'street', 'city', 'state', 'zip', 'approved'
    );
    private const PERMISSION = 'trs_organizations-r';

    private $name;
    private $type;
    private $phone;
    private $email;
    private $street;
    private $city;
    private $state;
    private $zip;
    private $approved;

    private $result = NULL;
    private $error = NULL;

    /**
     * SearchOrganizations constructor.
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        foreach(self::PARAMS as $param)
        {
            // Assign missing values as placeholders
            if(!isset($args[$param]) OR strlen($args[$param] === 0))
                $args[$param] = '%';
        }

        // Assign multi-value params as empty arrays if they were not provided as arrays
        if(!is_array($args['type']))
            $args['type'] = array();
        if(!is_array($args['approved']))
            $args['approved'] = array();

        $this->name = $args['name'];
        $this->type = $args['type'];
        $this->phone = $args['phone'];
        $this->email = $args['email'];
        $this->street = $args['street'];
        $this->city = $args['city'];
        $this->state = $args['state'];
        $this->zip = $args['zip'];
        $this->approved = $args['approved'];
    }

    /**
     * Executes the instructions of the command
     * @return bool Was the command successful?
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function execute(): bool
    {
        CurrentUserController::validatePermission(self::PERMISSION);

        $this->result = OrganizationDatabaseHandler::select($this->name, $this->type, $this->phone, $this->email, $this->street,
            $this->city, $this->state, $this->zip, $this->approved);

        return is_array($this->result);
    }

    /**
     * @return Organization[] The output of a successful command, defined by the command
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return MercuryException|null
     */
    public function getError(): ?MercuryException
    {
        return $this->error;
    }
}