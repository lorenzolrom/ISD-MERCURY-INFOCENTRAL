<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/03/2019
 * Time: 12:10 PM
 */


namespace extensions\itsm\utilities;

use extensions\itsm\business\WebServerOperator;
use extensions\itsm\ExtConfig;
use extensions\itsm\models\VHost;

/**
 * Class WebLogFileRetriever
 *
 * Obtains web logs from the local system
 *
 * @package utilities
 */
class WebLogFileRetriever
{
    /**
     * @param string $path
     * @return array
     */
    public static function getLogFileList(string $path): array
    {
        $logs = array('access' => array(), 'error' => array());
        
        if(!self::validPath($path) OR !is_dir($path))
            return $logs;

        foreach(scandir($path . '/') as $log)
        {
            if(substr($log, 0, 6) == 'access')
                $logs['access'][] = $log;
            else if(substr($log, 0, 5) == 'error')
                $logs['error'][] = $log;
        }

        return $logs;
    }

    /**
     * @param string $path
     * @return string|null
     */
    public static function getLogContents(string $path): ?string
    {
        if(!self::validPath($path) OR !is_file($path))
            return NULL;

        $logContents = "";

        $file = fopen($path, 'r');
        while($line = fgets($file))
            $logContents .= $line . "\n";

        fclose($file);

        return $logContents;
    }

    /**
     * @param string $path
     * @return bool
     */
    private static function validPath(string $path): bool
    {
        // Not allowed to go up in directories
        if(strpos($path, '..') !== FALSE)
            return FALSE;

        foreach(ExtConfig::OPTIONS['validWebLogPaths'] as $validPath)
        {
            if(substr($path, 0, strlen($validPath)) === $validPath)
                return TRUE;
        }

        return FALSE;
    }

    /**
     * Retrieve the logs from remote servers using information about the VHost's host
     * @param VHost $vHost
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function _getLogFileList(VHost $vHost)
    {
        // Retrieve the host of the website
        $server = WebServerOperator::get($vHost->getHost());
        $remoteAddress = $server->getIpAddress();

        // TODO SSH into server using a shared key

        // TODO append vhost logpath to server's logpath
        // Remove trailing / if present from server logpath
        // Add leading /, if not present, to vhost logpath
        // Remote the server logpath if it was included in the vhost
        // pull the list of log files
    }
}
