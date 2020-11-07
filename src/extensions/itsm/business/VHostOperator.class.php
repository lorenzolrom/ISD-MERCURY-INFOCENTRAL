<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 9:29 AM
 */


namespace extensions\itsm\business;


use business\AttributeOperator;
use business\Operator;
use database\AttributeDatabaseHandler;
use extensions\itsm\database\ApplicationDatabaseHandler;
use extensions\itsm\database\HostDatabaseHandler;
use extensions\itsm\database\RegistrarDatabaseHandler;
use extensions\itsm\database\VHostDatabaseHandler;
use exceptions\EntryInUseException;
use exceptions\ValidationException;
use models\Attribute;
use extensions\itsm\models\VHost;
use utilities\HistoryRecorder;
use extensions\itsm\utilities\WebLogFileRetriever;

class VHostOperator extends Operator
{
    /**
     * @param int $id
     * @return \extensions\itsm\models\VHost
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getVHost(int $id):VHost
    {
        return VHostDatabaseHandler::selectById($id);
    }

    /**
     * @param string $domain
     * @param string $subdomain
     * @param string $name
     * @param string $host
     * @param string $registrarCode
     * @param array $status
     * @return VHost[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(string $domain = "%", string $subdomain = "%", string $name = "%", string $host = "%",
                                  string $registrarCode = "%", $status = array()): array
    {
        return VHostDatabaseHandler::select($domain, $subdomain, $name, $host, $registrarCode, $status);
    }

    /**
     * @return Attribute[]
     * @throws \exceptions\DatabaseException
     */
    public static function getStatuses(): array
    {
        return AttributeDatabaseHandler::select('itsm', VHost::STATUS_ATTRIBUTE_TYPE);
    }

    /**
     * @param string|null $subdomain
     * @param string|null $domain
     * @param string|null $name
     * @param string|null $hostIP
     * @param string|null $registrarCode
     * @param string|null $statusCode
     * @param string|null $renewCost
     * @param string|null $registerDate
     * @param string|null $expireDate
     * @param string|null $notes
     * @param string|null $webRoot
     * @param string|null $logPath
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function createVHost(?string $subdomain, ?string $domain, ?string $name, ?string $systemName,
                                 ?string $registrarCode, ?string $statusCode, ?string $renewCost, ?string $registerDate,
                                 ?string $expireDate, ?string $notes,  ?string $webRoot, ?string $logPath): array
    {
        $errors = self::validateSubmission($subdomain, $domain, $name, $systemName, $registrarCode, $statusCode, $renewCost,
            $registerDate, $expireDate);

        if(!empty($errors))
            return array('errors' => $errors);

        if(strlen($expireDate) === 0)
            $expireDate = NULL;

        if(strlen($webRoot) === 0)
            $webRoot = NULL;

        if(strlen($logPath) === 0)
            $logPath = NULL;

        // Status code
        $status = AttributeOperator::idFromCode('itsm', 'wdns', $statusCode);

        // Host from system name
        $host = HostDatabaseHandler::selectIdFromSystemName($systemName);

        // Registrar
        $registrar = RegistrarDatabaseHandler::selectIdByCode($registrarCode);

        $vhost = VHostDatabaseHandler::insert($domain, $subdomain, $name, $host, $registrar, $status, (float)$renewCost, (string)$notes, $registerDate, $expireDate, $webRoot, $logPath);

        HistoryRecorder::writeHistory('ITSM_VHost', HistoryRecorder::CREATE, $vhost->getId(), $vhost, array(
            'domain' => $domain,
            'subdomain' => $subdomain,
            'name' => $name,
            'host' => $host,
            'registrar' => $registrar,
            'status' => $status,
            'renewCost' => (float)$renewCost,
            'notes' => (string)$notes,
            'registerDate' => $registerDate,
            'expireDate' => $expireDate,
            'webRoot' => $webRoot,
            'logPath' => $logPath
        ));

        return array('id' => $vhost->getId());
    }

    /**
     * @param VHost $vhost
     * @param string|null $subdomain
     * @param string|null $domain
     * @param string|null $name
     * @param string|null $systemName
     * @param string|null $registrarCode
     * @param string|null $statusCode
     * @param string|null $renewCost
     * @param string|null $registerDate
     * @param string|null $expireDate
     * @param string|null $notes
     * @param string|null $webRoot
     * @param string|null $logPath
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function updateVHost(VHost $vhost, ?string $subdomain, ?string $domain, ?string $name, ?string $systemName,
                                        ?string $registrarCode, ?string $statusCode, ?string $renewCost, ?string $registerDate,
                                        ?string $expireDate, ?string $notes, ?string $webRoot, ?string $logPath): array
    {
        $errors = self::validateSubmission($subdomain, $domain, $name, $systemName, $registrarCode, $statusCode, $renewCost,
            $registerDate, $expireDate, $vhost);

        if(!empty($errors))
            return array('errors' => $errors);

        if(strlen($expireDate) === 0)
            $expireDate = NULL;

        if(strlen($webRoot) === 0)
            $webRoot = NULL;

        if(strlen($logPath) === 0)
            $logPath = NULL;

        // Status code
        $status = AttributeOperator::idFromCode('itsm', 'wdns', $statusCode);

        // Host from systme name
        $host = HostDatabaseHandler::selectIdFromSystemName($systemName);

        // Registrar
        $registrar = RegistrarDatabaseHandler::selectIdByCode($registrarCode);

        HistoryRecorder::writeHistory('ITSM_VHost', HistoryRecorder::MODIFY, $vhost->getId(), $vhost, array(
            'domain' => $domain,
            'subdomain' => $subdomain,
            'name' => $name,
            'host' => $host,
            'registrar' => $registrar,
            'status' => $status,
            'renewCost' => (float)$renewCost,
            'notes' => (string)$notes,
            'registerDate' => $registerDate,
            'expireDate' => $expireDate,
            'webRoot' => $webRoot,
            'logPath' => $logPath
        ));

        $vhost = VHostDatabaseHandler::update($vhost->getId(), $domain, $subdomain, $name, $host, $registrar, $status, (float)$renewCost, (string)$notes, $registerDate, $expireDate, $webRoot, $logPath);

        return array('id' => $vhost->getId());
    }

    /**
     * @param VHost $vhost
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws EntryInUseException
     */
    public static function deleteVHost(VHost $vhost): bool
    {
        if(ApplicationDatabaseHandler::doApplicationsReferenceVHost($vhost->getId()))
            throw new EntryInUseException(EntryInUseException::MESSAGES[EntryInUseException::ENTRY_IN_USE], EntryInUseException::ENTRY_IN_USE);

        HistoryRecorder::writeHistory('ITSM_VHost', HistoryRecorder::DELETE, $vhost->getId(), $vhost);

        return VHostDatabaseHandler::delete($vhost->getId());
    }

    /**
     * @param VHost $vHost
     * @return array
     */
    public static function getLogFiles(VHost $vHost): array
    {
        if($vHost->getLogPath() === NULL)
            return array();

        return WebLogFileRetriever::getLogFileList($vHost->getLogPath());
    }

    /**
     * @param string|null $subdomain
     * @param string|null $domain
     * @param string|null $name
     * @param string|null $systemName
     * @param string|null $registrarCode
     * @param string|null $statusCode
     * @param string|null $renewCost
     * @param string|null $registerDate
     * @param string|null $expireDate
     * @param VHost|null $vhost
     * @return array
     * @throws \exceptions\DatabaseException
     */
    private static function validateSubmission(?string $subdomain, ?string $domain, ?string $name, ?string $systemName,
                                               ?string $registrarCode, ?string $statusCode, ?string $renewCost,
                                               ?string $registerDate, ?string $expireDate, ?VHost $vhost = NULL): array
    {
        $errors = array();

        // Subdomain
        if($vhost === NULL OR $vhost->getSubdomain() != $subdomain OR $vhost->getDomain() != $domain)
        {
            try{VHost::validateSubDomain($subdomain, $domain);}
            catch(ValidationException $e){$errors[] = $e->getMessage();}
        }

        // Domain
        try{VHost::validateDomain($domain);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // name
        try{VHost::validateName($name);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // server hostname
        try{VHost::validateHostSystemName($systemName);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // status code
        try{VHost::validateStatusCode($statusCode);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // renew cost
        try{VHost::validateRenewCost($renewCost);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // registrar code
        try{VHost::validateRegistrarCode($registrarCode);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // register date
        try{VHost::validateRegisterDate($registerDate);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // expire date
        try{VHost::validateExpireDate($expireDate);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        return $errors;
    }
}
