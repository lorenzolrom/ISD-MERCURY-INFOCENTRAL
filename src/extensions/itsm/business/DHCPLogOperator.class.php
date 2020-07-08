<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 9/18/2019
 * Time: 6:53 PM
 */


namespace extensions\itsm\business;


use business\Operator;
use extensions\itsm\ExtConfig;
use utilities\Validator;

class DHCPLogOperator extends Operator
{
    private const DEFAULT_LINES = 1000; // Because of how TAIL works this will return 1000 lines..

    // Used to convert month abbreviations from log file into two-digit numbers
    private const MONTH_CONVERSIONS = array(
        'Jan' => '01',
        'Feb' => '02',
        'Mar' => '03',
        'Apr' => '04',
        'May' => '05',
        'Jun' => '06',
        'Jul' => '07',
        'Aug' => '08',
        'Sep' => '09',
        'Oct' => '10',
        'Nov' => '11',
        'Dec' => '12'
    );

    /**
     * @param string $query
     * @param string|null $lines
     * @return string[]
     */
    public static function getDHCPLog(string $query, ?string $lines = NULL): array
    {
        $query = trim($query); // Remove whitespace

        if($lines === NULL)
            $lines = self::DEFAULT_LINES;

        // Only allow alnum, :, and .
        if(!Validator::alnumColonDotOnly($query) AND strlen($query) !== 0)
            return array();

        $query = strtolower($query); // Convert MACs to lower-case.

        $rawLog = shell_exec('bash ' . dirname(__FILE__) . '/../utilities/dhcplog.sh ' .
            ExtConfig::OPTIONS['dhcpUser'] . ' ' . ExtConfig::OPTIONS['dhcpServer'] . ' ' .
            \Config::OPTIONS['sshKeyPath'] . ' ' . ExtConfig::OPTIONS['dhcpLogPath'] . ' \'' . $query . '\' ' . ((int)$lines + 1));

        $logLines = explode('dhcpd:', $rawLog);
        array_shift($logLines);

        $log = array();

        foreach($logLines as $line)
        {
            $parts = explode(' ', trim($line));

            $type = $parts[0];

            $entry = array();
            $entry['type'] = $type;

            if($type === 'DHCPREQUEST')
            {
                $line = substr($line, 16);

                // from
                $parts = explode('from', $line);
            }
            else if($type === 'DHCPACK')
            {
                $line = substr($line, 11);

                // to
                $parts = explode('to', trim($line));
            }
            else
                continue;

            $entry['ip'] = trim($parts[0]);

            $parts = explode('via', trim($parts[1]));

            $entry['mac'] = trim($parts[0]);

            // Remaining parts should be: interface Month Date Timestamp DHCPServer
            $parts = explode(' ', trim($parts[1]));
            $entry['interface'] = $parts[0];

            if(sizeof($parts) < 5) // Not a valid entry
                continue;

            $entry['date'] = self::MONTH_CONVERSIONS[$parts[1]] . '-' . str_pad($parts[2], 2, '0', STR_PAD_LEFT) . ' ' . $parts[3];
            $entry['server'] = $parts[4];

            $log[] = $entry;
        }

        return $log;
    }
}
