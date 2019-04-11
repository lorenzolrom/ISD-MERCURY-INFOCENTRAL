<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 9:28 AM
 */


namespace business;


use database\AttributeDatabaseHandler;
use models\Attribute;

class AttributeOperator extends Operator
{
    /**
     * @param int $id
     * @return Attribute
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getAttribute(int $id): Attribute
    {
        return AttributeDatabaseHandler::selectById($id);
    }
}