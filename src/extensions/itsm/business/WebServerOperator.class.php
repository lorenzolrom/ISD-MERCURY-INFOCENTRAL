<?php


namespace extensions\itsm\business;


use business\Operator;
use exceptions\ValidationError;
use extensions\itsm\database\HostDatabaseHandler;
use extensions\itsm\database\WebServerDatabaseHandler;
use extensions\itsm\models\WebServer;
use utilities\HistoryRecorder;

class WebServerOperator extends Operator
{
    /**
     * @param array $vals
     * @return array
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function create(array $vals): array
    {
        self::validate('extensions\itsm\models\WebServer', $vals);

        // Check if host has already been assigned
        if(WebServerDatabaseHandler::isHostInUse((int)$vals['host']))
            throw new ValidationError(array('Host already assigned to web server'));

        // Check if host exists
        if(!HostDatabaseHandler::selectIPAndNameById((int)$vals['host']))
            throw new ValidationError(array('Host does not exist'));

        $webServer = WebServerDatabaseHandler::insert((int)$vals['host'], $vals['webroot'], $vals['logpath'], $vals['confpath']);

        HistoryRecorder::writeHistory('ITSM_WebServer', HistoryRecorder::CREATE, $webServer->getHost(), $webServer);
        return array('host' => $webServer->getHost());
    }

    /**
     * @param WebServer $ws
     * @param array $vals
     * @return bool
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function update(WebServer $ws, array $vals): bool
    {
        self::validate('extensions\itsm\models\WebServer', $vals);

        // Host change not allowed
        if($ws->getHost() !== (int)$vals['host'])
            throw new ValidationError(array('Host cannot be changed, delete and re-create on different host'));

        HistoryRecorder::writeHistory('ITSM_WebServer', HistoryRecorder::MODIFY, $ws->getHost(), $ws, $vals);
        return WebServerDatabaseHandler::update($ws->getHost(), $vals['webroot'], $vals['logpath'], $vals['confpath']);
    }

    /**
     * @param WebServer $ws
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function delete(WebServer $ws): bool
    {
        HistoryRecorder::writeHistory('ITSM_WebServer', HistoryRecorder::DELETE, $ws->getHost(), $ws);
        return WebServerDatabaseHandler::delete($ws->getHost());
    }

    /**
     * @param int $host
     * @return WebServer
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function get(int $host): WebServer
    {
        return WebServerDatabaseHandler::selectByHost($host);
    }

    /**
     * @param array $search
     * @return WebServer[]
     * @throws \exceptions\DatabaseException
     */
    public static function getSearchResults(array $search): array
    {
        return WebServerDatabaseHandler::select($search['ipAddress'], $search['systemName']);
    }
}