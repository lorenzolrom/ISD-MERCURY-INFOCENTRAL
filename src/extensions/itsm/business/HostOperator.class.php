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
 * Time: 6:28 PM
 */


namespace extensions\itsm\business;


use business\Operator;
use extensions\itsm\database\ApplicationDatabaseHandler;
use extensions\itsm\database\AssetDatabaseHandler;
use extensions\itsm\database\HostDatabaseHandler;
use extensions\itsm\database\VHostDatabaseHandler;
use exceptions\EntryInUseException;
use exceptions\ValidationException;
use extensions\itsm\models\Host;
use utilities\HistoryRecorder;

class HostOperator extends Operator
{
    /**
     * @param int $id
     * @return Host
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getHost(int $id): Host
    {
        return HostDatabaseHandler::selectById($id);
    }

    /**
     * @param string $assetTag
     * @param string $ipAddress
     * @param string $macAddress
     * @param string $systemName
     * @param string $systemCPU
     * @param string $systemRAM
     * @param string $systemOS
     * @param string $systemDomain
     * @return Host[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(string $assetTag = '%', string $ipAddress = '%', string $macAddress = '%',
                                  string $systemName = '%', string $systemCPU = '%', string $systemRAM = '%',
                                  string $systemOS = '%', string $systemDomain = '%'): array
    {
        return HostDatabaseHandler::select($assetTag, $ipAddress, $macAddress, $systemName, $systemCPU, $systemRAM,
            $systemOS, $systemDomain);
    }

    /**
     * @param string|null $assetTag
     * @param string|null $ipAddress
     * @param string|null $macAddress
     * @param string|null $systemName
     * @param string|null $systemCPU
     * @param string|null $systemRAM
     * @param string|null $systemOS
     * @param string|null $systemDomain
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function createHost(?string $assetTag, ?string $ipAddress, ?string $macAddress,
                                      ?string $systemName, ?string $systemCPU, ?string $systemRAM,
                                      ?string $systemOS, ?string $systemDomain): array
    {
        $errors = self::validateSubmission($assetTag, $ipAddress, $macAddress, $systemName, $systemCPU, $systemRAM,
            $systemOS, $systemDomain);

        if(!empty($errors))
            return array('errors' => $errors);

        $host = HostDatabaseHandler::insert(AssetDatabaseHandler::selectIdByAssetTag($assetTag), $ipAddress,
            $macAddress, $systemName, $systemCPU, $systemRAM, $systemOS, $systemDomain);

        HistoryRecorder::writeHistory('ITSM_Host', HistoryRecorder::CREATE, $host->getId(), $host,
            array('asset' => AssetDatabaseHandler::selectIdByAssetTag($assetTag), 'ipAddress' => $ipAddress,
                'macAddress' => $macAddress, 'systemName' => $systemName, 'systemCPU' => $systemCPU,
                'systemRAM' => $systemRAM, 'systemOS' => $systemOS, 'systemDomain' => $systemDomain));

        return array('id' => $host->getId());
    }

    /**
     * @param Host $host
     * @param string|null $assetTag
     * @param string|null $ipAddress
     * @param string|null $macAddress
     * @param string|null $systemName
     * @param string|null $systemCPU
     * @param string|null $systemRAM
     * @param string|null $systemOS
     * @param string|null $systemDomain
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function updateHost(Host $host, ?string $assetTag, ?string $ipAddress, ?string $macAddress,
                                      ?string $systemName, ?string $systemCPU, ?string $systemRAM,
                                      ?string $systemOS, ?string $systemDomain): array
    {
        $errors = self::validateSubmission($assetTag, $ipAddress, $macAddress, $systemName, $systemCPU, $systemRAM,
            $systemOS, $systemDomain, $host);

        if(!empty($errors))
            return array('errors' => $errors);

        HistoryRecorder::writeHistory('ITSM_Host', HistoryRecorder::MODIFY, $host->getId(), $host,
            array('asset' => AssetDatabaseHandler::selectIdByAssetTag($assetTag), 'ipAddress' => $ipAddress,
                'macAddress' => $macAddress, 'systemName' => $systemName, 'systemCPU' => $systemCPU,
                'systemRAM' => $systemRAM, 'systemOS' => $systemOS, 'systemDomain' => $systemDomain));

        $newHost = HostDatabaseHandler::update($host->getId(), AssetDatabaseHandler::selectIdByAssetTag($assetTag), $ipAddress,
            $macAddress, $systemName, $systemCPU, $systemRAM, $systemOS, $systemDomain);

        return array('id' => $newHost->getId());
    }

    /**
     * @param Host $host
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws EntryInUseException
     */
    public static function deleteHost(Host $host): bool
    {
        if(ApplicationDatabaseHandler::doApplicationsReferenceHost($host->getId()) OR VHostDatabaseHandler::doVHostsReferenceHost($host->getId()))
            throw new EntryInUseException(EntryInUseException::MESSAGES[EntryInUseException::ENTRY_IN_USE], EntryInUseException::ENTRY_IN_USE);

        HistoryRecorder::writeHistory('ITSM_Host', HistoryRecorder::DELETE, $host->getId(), $host);

        return HostDatabaseHandler::delete($host->getId());
    }

    /**
     * @param int $id
     * @return string
     * @throws \exceptions\DatabaseException
     */
    public static function getDisplayNameById(int $id): string
    {
        $attributes = HostDatabaseHandler::selectIPAndNameById($id);

        return $attributes['systemName'] . " ({$attributes['ipAddress']})";
    }

    /**
     * @param int $id
     * @return string
     * @throws \exceptions\DatabaseException
     */
    public static function getIPAddressById(int $id): string
    {
        return HostDatabaseHandler::selectIPAndNameById($id)['ipAddress'];
    }

    /**
     * @param string|null $assetTag
     * @param string|null $ipAddress
     * @param string|null $macAddress
     * @param string|null $systemName
     * @param string|null $systemCPU
     * @param string|null $systemRAM
     * @param string|null $systemOS
     * @param string|null $systemDomain
     * @param Host|null $host
     * @return array
     * @throws \exceptions\DatabaseException
     */
    private static function validateSubmission(?string $assetTag, ?string $ipAddress, ?string $macAddress,
                                               ?string $systemName, ?string $systemCPU, ?string $systemRAM,
                                               ?string $systemOS, ?string $systemDomain, ?Host $host = NULL): array
    {
        $errors = array();

        // ASSET TAG
        try{Host::validateAssetTag($assetTag);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // IP
        if($host === NULL OR $host->getIpAddress() != $ipAddress)
        {
            try{Host::validateIPAddress($ipAddress);}
            catch(ValidationException $e){$errors[] = $e->getMessage();}
        }

        // MAC
        if($host === NULL OR $host->getMacAddress() != $macAddress)
        {
            try{Host::validateMacAddress($macAddress);}
            catch(ValidationException $e){$errors[] = $e->getMessage();}
        }

        // SYSTEM NAME
        try{Host::validateSystemName($systemName);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // SYSTEM CPU
        try{Host::validateSystemCPU($systemCPU);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // SYSTEM RAM
        try{Host::validateSystemRAM($systemRAM);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // SYSTEM OS
        try{Host::validateSystemOS($systemOS);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // SYSTEM DOMAIN
        try{Host::validateSystemDomain($systemDomain);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        return $errors;
    }
}
