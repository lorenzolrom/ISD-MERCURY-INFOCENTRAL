<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 9:29 AM
 */


namespace business\itsm;


use business\Operator;
use database\itsm\RegistrarDatabaseHandler;
use models\itsm\Registrar;

class RegistrarOperator extends Operator
{
    /**
     * @param int $id
     * @return Registrar
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getRegistrar(int $id): Registrar
    {
        return RegistrarDatabaseHandler::selectById($id);
    }

    /**
     * @param string $code
     * @param string $name
     * @return Registrar[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(string $code = '%', string $name = '%'): array
    {
        return RegistrarDatabaseHandler::select($code, $name);
    }
}