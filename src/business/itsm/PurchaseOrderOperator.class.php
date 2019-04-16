<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/15/2019
 * Time: 8:46 AM
 */


namespace business\itsm;


use business\Operator;
use database\itsm\PurchaseOrderDatabaseHandler;

class PurchaseOrderOperator extends Operator
{
    /**
     * @param int $id
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function numberFromId(int $id): ?int
    {
        return PurchaseOrderDatabaseHandler::numberFromId($id);
    }
}