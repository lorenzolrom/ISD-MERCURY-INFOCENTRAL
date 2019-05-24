<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/24/2019
 * Time: 12:23 PM
 */


namespace business\lockshop;


use business\Operator;
use database\lockshop\SystemDatabaseHandler;
use models\lockshop\System;

class SystemOperator extends Operator
{
    /**
     * @param int $id
     * @return System
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getSystem(int $id): System
    {
        return SystemDatabaseHandler::selectById($id);
    }
}