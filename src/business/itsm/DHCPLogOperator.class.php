<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 9/18/2019
 * Time: 6:53 PM
 */


namespace business\itsm;


use business\Operator;

class DHCPLogOperator extends Operator
{
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
     * @return string[]
     */
    public static function getDHCPLog(): array
    {
        $rawLog = shell_exec('bash ' . dirname(__FILE__) . '/../../utilities/dhcplog.sh ' .
            \Config::OPTIONS['dhcpUser'] . ' ' . \Config::OPTIONS['dhcpServer'] . ' ' .
            \Config::OPTIONS['sshKeyPath'] . ' ' . \Config::OPTIONS['dhcpLogPath']);

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