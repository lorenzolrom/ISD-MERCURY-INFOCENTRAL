<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/27/2019
 * Time: 5:49 PM
 */


namespace extensions\itsm\models;


use extensions\itsm\database\AssetDatabaseHandler;
use extensions\itsm\database\HostDatabaseHandler;
use exceptions\ValidationException;
use models\Model;
use extensions\itsm\utilities\Pinger;
use utilities\Validator;

class Host extends Model
{
    private const MESSAGES = array(
        'ASSET_TAG' => 'Asset tag not found',
        'IP_ADDRESS_LENGTH' => 'I.P. must be between 1 and 39 characters',
        'IP_ADDRESS_IN_USE' => 'I.P. address is already in use',
        'IP_ADDRESS_INVALID' => 'I.P. address is not valid',
        'MAC_ADDRESS_LENGTH' => 'MAC address must be 17 characters',
        'MAC_ADDRESS_FORMAT' => 'MAC address must consist of letters, numbers, and : only',
        'MAC_ADDRESS_IN_USE' => 'MAC address is already in use',
        'SYSTEM_NAME' => 'System name must be no greater than 64 characters',
        'SYSTEM_CPU' => 'System CPU must be no greater than 64 characters',
        'SYSTEM_RAM' => 'System RAM must be no greater than 64 characters',
        'SYSTEM_OS' => 'System O.S. must be no greater than 64 characters',
        'SYSTEM_DOMAIN' => 'System domain must be no greater than 64 characters'
    );

    private $id;
    private $asset;
    private $ipAddress;
    private $macAddress;
    private $systemName;
    private $systemCPU;
    private $systemRAM;
    private $systemOS;
    private $systemDomain;

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
    public function getAsset(): int
    {
        return $this->asset;
    }

    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    /**
     * @return string
     */
    public function getMacAddress(): string
    {
        return $this->macAddress;
    }

    /**
     * @return string
     */
    public function getSystemName(): string
    {
        return $this->systemName;
    }

    /**
     * @return string
     */
    public function getSystemCPU(): string
    {
        return $this->systemCPU;
    }

    /**
     * @return string
     */
    public function getSystemRAM(): string
    {
        return $this->systemRAM;
    }

    /**
     * @return string
     */
    public function getSystemOS(): string
    {
        return $this->systemOS;
    }

    /**
     * @return string
     */
    public function getSystemDomain(): string
    {
        return $this->systemDomain;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return Pinger::ping($this->ipAddress);
    }

    /**
     * @param string|null $assetTag
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateAssetTag(?string $assetTag): bool
    {
        // not null
        if($assetTag === NULL)
            throw new ValidationException(self::MESSAGES['ASSET_TAG'], ValidationException::VALUE_IS_NULL);

        // exists in asset table
        if(AssetDatabaseHandler::selectIdByAssetTag($assetTag) === NULL)
            throw new ValidationException(self::MESSAGES['ASSET_TAG'], ValidationException::VALUE_IS_NOT_VALID);

        return TRUE;
    }

    /**
     * @param string|null $ipAddress
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateIPAddress(?string $ipAddress): bool
    {
        // not null
        if($ipAddress === NULL)
            throw new ValidationException(self::MESSAGES['IP_ADDRESS_LENGTH'], ValidationException::VALUE_IS_NULL);

        // not in use
        if(HostDatabaseHandler::isIPAddressInUse($ipAddress))
            throw new ValidationException(self::MESSAGES['IP_ADDRESS_IN_USE'], ValidationException::VALUE_ALREADY_TAKEN);

        // Greater than one character
        if(strlen($ipAddress) < 1)
            throw new ValidationException(self::MESSAGES['IP_ADDRESS_LENGTH'], ValidationException::VALUE_TOO_SHORT);

        if(!filter_var($ipAddress, FILTER_VALIDATE_IP))
            throw new ValidationException(self::MESSAGES['IP_ADDRESS_INVALID'], ValidationException::VALUE_IS_NOT_VALID);

        // not greater than 39 characters
        if(strlen($ipAddress) > 39)
        {
            throw new ValidationException(self::MESSAGES['IP_ADDRESS_LENGTH'], ValidationException::VALUE_TOO_LONG);
        }

        return TRUE;
    }

    /**
     * @param string|null $macAddress
     * @return bool
     * @throws ValidationException
     * @throws \exceptions\DatabaseException
     */
    public static function validateMacAddress(?string $macAddress): bool
    {
        // not null
        if($macAddress === NULL)
            throw new ValidationException(self::MESSAGES['MAC_ADDRESS_LENGTH'], ValidationException::VALUE_IS_NULL);

        // not in use
        if(HostDatabaseHandler::isMACAddressInUse($macAddress))
            throw new ValidationException(self::MESSAGES['MAC_ADDRESS_IN_USE'], ValidationException::VALUE_ALREADY_TAKEN);

        // valid MAC
        if(!Validator::validMACAddress($macAddress))
            throw new ValidationException(self::MESSAGES['MAC_ADDRESS_FORMAT'], ValidationException::VALUE_IS_NOT_VALID);

        // exactly 17 characters
        if(strlen($macAddress) !== 17)
        {
            throw new ValidationException(self::MESSAGES['MAC_ADDRESS_LENGTH'], ValidationException::VALUE_IS_NOT_VALID);
        }

        return TRUE;
    }

    /**
     * @param string|null $systemName
     * @return bool
     * @throws ValidationException
     */
    public static function validateSystemName(?string $systemName): bool
    {
        // not null
        if($systemName === NULL)
            throw new ValidationException(self::MESSAGES['SYSTEM_NAME'], ValidationException::VALUE_IS_NULL);

        // not greater than 64 characters
        if(strlen($systemName) > 64)
            throw new ValidationException(self::MESSAGES['SYSTEM_NAME'], ValidationException::VALUE_TOO_LONG);

        return TRUE;
    }

    /**
     * @param string|null $systemCPU
     * @return bool
     * @throws ValidationException
     */
    public static function validateSystemCPU(?string $systemCPU): bool
    {
        // not null
        if($systemCPU === NULL)
            throw new ValidationException(self::MESSAGES['SYSTEM_CPU'], ValidationException::VALUE_IS_NULL);

        // not greater than 64 characters
        if(strlen($systemCPU) > 64)
            throw new ValidationException(self::MESSAGES['SYSTEM_CPU'], ValidationException::VALUE_TOO_LONG);

        return TRUE;
    }

    /**
     * @param string|null $systemRAM
     * @return bool
     * @throws ValidationException
     */
    public static function validateSystemRAM(?string $systemRAM): bool
    {
        // not null
        if($systemRAM === NULL)
            throw new ValidationException(self::MESSAGES['SYSTEM_RAM'], ValidationException::VALUE_IS_NULL);

        // not greater than 64 characters
        if(strlen($systemRAM) > 64)
            throw new ValidationException(self::MESSAGES['SYSTEM_RAM'], ValidationException::VALUE_TOO_LONG);

        return TRUE;
    }

    /**
     * @param string|null $systemOS
     * @return bool
     * @throws ValidationException
     */
    public static function validateSystemOS(?string $systemOS): bool
    {
        // not null
        if($systemOS === NULL)
            throw new ValidationException(self::MESSAGES['SYSTEM_OS'], ValidationException::VALUE_IS_NULL);

        // not greater than 64 characters
        if(strlen($systemOS) > 64)
            throw new ValidationException(self::MESSAGES['SYSTEM_OS'], ValidationException::VALUE_TOO_LONG);

        return TRUE;
    }

    /**
     * @param string|null $systemDomain
     * @return bool
     * @throws ValidationException
     */
    public static function validateSystemDomain(?string $systemDomain): bool
    {
        // not null
        if($systemDomain === NULL)
            throw new ValidationException(self::MESSAGES['SYSTEM_DOMAIN'], ValidationException::VALUE_IS_NULL);

        // not greater than 64 characters
        if(strlen($systemDomain) > 64)
            throw new ValidationException(self::MESSAGES['SYSTEM_DOMAIN'], ValidationException::VALUE_TOO_LONG);

        return TRUE;
    }
}
