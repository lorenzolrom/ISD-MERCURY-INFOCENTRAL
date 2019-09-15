<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 5:21 PM
 */


namespace models\itsm;


use database\AttributeDatabaseHandler;
use database\itsm\HostDatabaseHandler;
use database\itsm\RegistrarDatabaseHandler;
use database\itsm\VHostDatabaseHandler;
use exceptions\ValidationException;
use models\Model;
use utilities\Validator;

class VHost extends Model
{
    private const MESSAGES = array(
        'DOMAIN_REQUIRED' => 'Domain required',
        'SUBDOMAIN_REQUIRED' => 'Subdomain required',
        'DOMAIN_INVALID' => 'Domain is invalid',
        'SUBDOMAIN_INVALID' => 'Subdomain is invalid',
        'SUBDOMAIN_TAKEN' => 'Subdomain already in use on domain',
        'NAME_LENGTH' => 'Name must be between 1 and 64 characters',
        'NAME_INVALID' => 'Name must consist of letters, numbers, -, and spaces only',
        'HOST_INVALID' => 'Host I.P. address not found',
        'REGISTRAR_INVALID' => 'Registrar not found',
        'STATUS' => 'Status is not valid',
        'RENEW COST' => 'Renew cost must be a non-negative number',
        'REGISTER_DATE' => 'Register date is not valid',
        'EXPIRE_DATE' => 'Expire date is not valid'
    );

    const STATUS_ATTRIBUTE_TYPE = "wdns";

    private $id;
    private $domain;
    private $subdomain;
    private $name;
    private $host;
    private $registrar;
    private $status;
    private $renewCost;
    private $notes;
    private $registerDate;
    private $expireDate;
    private $webRoot;
    private $logPath;

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
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getSubdomain(): string
    {
        return $this->subdomain;
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
    public function getHost(): int
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getRegistrar(): int
    {
        return $this->registrar;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return float
     */
    public function getRenewCost(): float
    {
        return $this->renewCost;
    }

    /**
     * @return string|null
     */
    public function getNotes(): string
    {
        return $this->notes;
    }

    /**
     * @return string
     */
    public function getRegisterDate(): string
    {
        return $this->registerDate;
    }

    /**
     * @return string|null
     */
    public function getExpireDate(): ?string
    {
        return $this->expireDate;
    }

    /**
     * @return mixed
     */
    public function getWebRoot()
    {
        return $this->webRoot;
    }

    /**
     * @return mixed
     */
    public function getLogPath()
    {
        return $this->logPath;
    }

    /**
     * @param string|null $domain
     * @return bool
     * @throws ValidationException
     */
    public static function validateDomain(?string $domain): bool
    {
        // not null
        if($domain === NULL)
            throw new ValidationException(self::MESSAGES['DOMAIN_REQUIRED'], ValidationException::VALUE_IS_NULL);

        // greater than 1 character
        if(strlen($domain) < 1)
            throw new ValidationException(self::MESSAGES['DOMAIN_REQUIRED'], ValidationException::VALUE_IS_NULL);

        // valid domain characters
        if(!Validator::validDomainName($domain))
            throw new ValidationException(self::MESSAGES['DOMAIN_INVALID'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }

    /**
     * @param string|null $subdomain
     * @param string|null $domain
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateSubDomain(?string $subdomain, ?string $domain): bool
    {
        // not null
        if($subdomain === NULL)
            throw new ValidationException(self::MESSAGES['SUBDOMAIN_REQUIRED'], ValidationException::VALUE_IS_NULL);

        // not in use on domain
        if(VHostDatabaseHandler::isSubdomainInUseOnDomain($domain === NULL ? '' : $domain, $subdomain))
            throw new ValidationException(self::MESSAGES['SUBDOMAIN_TAKEN'], ValidationException::VALUE_IS_NULL);

        // greater than 1 character
        if(strlen($subdomain) < 1)
            throw new ValidationException(self::MESSAGES['SUBDOMAIN_REQUIRED'], ValidationException::VALUE_IS_NULL);

        // valid domain characters
        if(!Validator::validDomainName($subdomain) AND $subdomain != '@' AND $subdomain != '*')
            throw new ValidationException(self::MESSAGES['SUBDOMAIN_INVALID'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }

    /**
     * @param string|null $name
     * @return bool
     * @throws ValidationException
     */
    public static function validateName(?string $name): bool
    {
        // not null
        if($name === NULL)
            throw new ValidationException(self::MESSAGES['NAME_LENGTH'], ValidationException::VALUE_IS_NULL);

        // between 1 and 64 characters
        if(strlen($name) < 1)
            throw new ValidationException(self::MESSAGES['NAME_LENGTH'], ValidationException::VALUE_TOO_SHORT);

        if(strlen($name) > 64)
            throw new ValidationException(self::MESSAGES['NAME_LENGTH'], ValidationException::VALUE_TOO_LONG);

        return TRUE;
    }

    /**
     * @param string|null $hostIP
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateHostIP(?string $hostIP): bool
    {
        // not null
        if($hostIP === NULL)
            throw new ValidationException(self::MESSAGES['HOST_INVALID'], ValidationException::VALUE_IS_NULL);

        // host exists with IP
        if(!HostDatabaseHandler::isIPAddressInUse($hostIP))
            throw new ValidationException(self::MESSAGES['HOST_INVALID'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }

    /**
     * @param string|null $registrarCode
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateRegistrarCode(?string $registrarCode): bool
    {
        // not null
        if($registrarCode === NULL)
            throw new ValidationException(self::MESSAGES['REGISTRAR_INVALID'], ValidationException::VALUE_IS_NULL);

        // registrar exists
        if(!RegistrarDatabaseHandler::codeInUse($registrarCode))
            throw new ValidationException(self::MESSAGES['REGISTRAR_INVALID'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }

    /**
     * @param string|null $statusCode
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateStatusCode(?string $statusCode): bool
    {
        // not null
        if($statusCode === NULL)
            throw new ValidationException(self::MESSAGES['STATUS'], ValidationException::VALUE_IS_NULL);

        // valid status
        if(!AttributeDatabaseHandler::isCodeValid('itsm', self::STATUS_ATTRIBUTE_TYPE, $statusCode))
            throw new ValidationException(self::MESSAGES['STATUS'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }

    /**
     * @param string|null $renewCost
     * @return bool
     * @throws ValidationException
     */
    public static function validateRenewCost(?string $renewCost): bool
    {
        // not null
        if($renewCost === NULL)
            throw new ValidationException(self::MESSAGES['RENEW COST'], ValidationException::VALUE_IS_NULL);

        if(!is_numeric($renewCost) OR $renewCost < 0)
            throw new ValidationException(self::MESSAGES['RENEW COST'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }

    /**
     * @param string|null $registerDate
     * @return bool
     * @throws ValidationException
     */
    public static function validateRegisterDate(?string $registerDate): bool
    {
        // not null
        if($registerDate === NULL)
            throw new ValidationException(self::MESSAGES['REGISTER_DATE'], ValidationException::VALUE_IS_NULL);

        // valid date
        if(!Validator::validDate($registerDate))
            throw new ValidationException(self::MESSAGES['REGISTER_DATE'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }

    /**
     * @param string|null $expireDate
     * @return bool
     * @throws ValidationException
     */
    public static function validateExpireDate(?string $expireDate): bool
    {
        if($expireDate !== NULL AND strlen($expireDate) !== 0 AND !Validator::validDate($expireDate))
            throw new ValidationException(self::MESSAGES['EXPIRE_DATE'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }
}