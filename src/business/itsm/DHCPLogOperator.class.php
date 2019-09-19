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
    public static function getDHCPLog(): string
    {
        $rawLog = shell_exec(dirname(__FILE__) . '../../Utilities/dhcplog.sh ' .
            \Config::OPTIONS['dhcpUser'] . ' ' . \Config::OPTIONS['dhcpServer'] . ' ' .
            \Config::OPTIONS['dhcpSSHKeyPath'] . ' ' . \Config::OPTIONS['dhcpLogPath']);

        return (string)$rawLog;
    }
}