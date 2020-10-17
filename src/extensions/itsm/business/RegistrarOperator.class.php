<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 9:29 AM
 */


namespace extensions\itsm\business;


use business\Operator;
use extensions\itsm\database\RegistrarDatabaseHandler;
use extensions\itsm\database\VHostDatabaseHandler;
use exceptions\EntryInUseException;
use exceptions\ValidationException;
use extensions\itsm\models\Registrar;
use utilities\HistoryRecorder;

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

    /**
     * @param string|null $code
     * @param string|null $name
     * @param string|null $url
     * @param string|null $phone
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function createRegistrar(?string $code, ?string $name, ?string $url, ?string $phone): array
    {
        $errors = self::validateSubmission($code, $name, $phone);

        if(!empty($errors))
            return array('errors' => $errors);

        $registrar = RegistrarDatabaseHandler::insert($code, $name, (string)$url, (string)$phone);

        HistoryRecorder::writeHistory('ITSM_Registrar', HistoryRecorder::CREATE, $registrar->getId(), $registrar, array(
            'code' => $code,
            'name' => $name,
            'url' => $url,
            'phone' => $phone
        ));

        return array('id' => $registrar->getId());
    }

    /**
     * @param Registrar $registrar
     * @param string|null $code
     * @param string|null $name
     * @param string|null $url
     * @param string|null $phone
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function updateRegistrar(Registrar $registrar, ?string $code, ?string $name, ?string $url, ?string $phone): array
    {
        $errors = self::validateSubmission($code, $name, $phone);

        if(!empty($errors))
            return array('errors' => $errors);

        HistoryRecorder::writeHistory('ITSM_Registrar', HistoryRecorder::MODIFY, $registrar->getId(), $registrar, array(
            'code' => $code,
            'name' => $name,
            'url' => $url,
            'phone' => $phone
        ));

        $newRegistrar = RegistrarDatabaseHandler::update($registrar->getId(), $code, $name, $url, $phone);

        return array('id' => $newRegistrar->getId());
    }

    /**
     * @param Registrar $registrar
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws EntryInUseException
     */
    public static function deleteRegistrar(Registrar $registrar): bool
    {
        // Check if V.Host uses registrar
        if(VHostDatabaseHandler::doVHostsReferenceRegistrar($registrar->getId()))
            throw new EntryInUseException(EntryInUseException::MESSAGES[EntryInUseException::ENTRY_IN_USE], EntryInUseException::ENTRY_IN_USE);

        HistoryRecorder::writeHistory('ITSM_Vendor', HistoryRecorder::DELETE, $registrar->getId(), $registrar);

        return RegistrarDatabaseHandler::delete($registrar->getId());
    }

    /**
     * @param int $id
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function nameFromId(int $id): ?string
    {
        return RegistrarDatabaseHandler::selectNameById($id);
    }

    /**
     * @param int $id
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function codeFromId(int $id): ?string
    {
        return RegistrarDatabaseHandler::selectCodeById($id);
    }

    /**
     * @param string|null $code
     * @param string|null $name
     * @param string|null $phone
     * @param Registrar|null $registrar
     * @return array
     * @throws \exceptions\DatabaseException
     */
    private static function validateSubmission(?string $code, ?string $name, ?string $phone, ?Registrar $registrar = NULL): array
    {
        $errors = array();

        // Code
        if($registrar === NULL OR $registrar->getCode() != $code)
        {
            try{Registrar::validateCode($code);}
            catch(ValidationException $e){$errors[] = $e->getMessage();}
        }

        // Name
        try{Registrar::validateName($name);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // URL (none)

        // Phone
        try{Registrar::validatePhone($phone);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        return $errors;
    }
}
