<?php


namespace extensions\itsm\models;


use models\Model;
use utilities\Validator;

class WebServer extends Model
{
    private const WEBROOT_RULES = array(
        'name' => 'Web Root',
        'unixpath' => TRUE,
        'lower' => 1,
        'null' => FALSE
    );

    private const LOGPATH_RULES = array(
        'name' => 'Log Path',
        'unixpath' => TRUE,
        'lower' => 1,
        'null' => FALSE
    );

    public $host; // INT, id of the Host this WebServer is running on
    public $webroot; // TEXT, the absolute path on the remote server that contains all site directories
    public $logpath; // TEXT, the absolute path on the remote server that contains all site logs

    // Variables from Host table that will be included from query join, immutable
    public $ipAddress; // I.P. address of remote server
    public $systemName; // Hostname of remote server

    /**
     * @return int
     */
    public function getHost(): int
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getWebroot(): string
    {
        return $this->webroot;
    }

    /**
     * @return string
     */
    public function getLogpath(): string
    {
        return $this->logpath;
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
    public function getSystemName() : string
    {
        return $this->systemName;
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateWebroot(?string $val): bool
    {
        return Validator::validate(self::WEBROOT_RULES, $val);
    }

    /**
     * @param string|null $val
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateLogpath(?string $val): bool
    {
        return Validator::validate(self::LOGPATH_RULES, $val);
    }
}