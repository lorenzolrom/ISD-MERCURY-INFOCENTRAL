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
use exceptions\ValidationException;
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

    /**
     * @param string $extension
     * @param string $type
     * @param int $id
     * @return Attribute
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getAttributeByExtensionTypeId(string $extension, string $type, int $id): Attribute
    {
        return AttributeDatabaseHandler::selectByExtensionTypeId($extension, $type, $id);
    }

    /**
     * @param int $id
     * @return string
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function nameFromId(int $id): string
    {
        return AttributeDatabaseHandler::selectNameById($id);
    }

    /**
     * @param string $extension
     * @param string $type
     * @param string $code
     * @return int|null
     * @throws \exceptions\DatabaseException
     */
    public static function idFromCode(string $extension, string $type, string $code): ?int
    {
        return AttributeDatabaseHandler::idFromCode($extension, $type, $code);
    }

    /**
     * @param int $id
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function codeFromId(int $id): ?string
    {
        return AttributeDatabaseHandler::codeFromId($id);
    }

    /**
     * @param string|null $extension
     * @param string|null $type
     * @param string|null $code
     * @param string|null $name
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function createAttribute(?string $extension, ?string $type, ?string $code, ?string $name): array
    {
        $errors = self::validateSubmission($extension, $type, $code, $name);

        if(!empty($errors))
            return array('errors' => $errors);

        return array('id' => AttributeDatabaseHandler::insert($extension, $type, $code, $name)->getId());
    }

    /**
     * @param Attribute $attribute
     * @param string|null $code
     * @param string|null $name
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function updateAttribute(Attribute $attribute, ?string $code, ?string $name): array
    {
        $errors = self::validateSubmission($attribute->getExtension(), $attribute->getType(), $code, $name, $attribute);

        if(!empty($errors))
            return array('errors' => $errors);

        return array('id' => AttributeDatabaseHandler::update($attribute->getId(), $code, $name)->getId());
    }

    /**
     * @param Attribute $attribute
     * @return bool
     * @throws \exceptions\EntryInUseException
     */
    public static function deleteAttribute(Attribute $attribute): bool
    {
        return AttributeDatabaseHandler::delete($attribute->getId());
    }

    /**
     * @param string|null $extension
     * @param string|null $type
     * @param string|null $code
     * @param string|null $name
     * @param Attribute|null $attribute
     * @return array
     * @throws \exceptions\DatabaseException
     */
    private static function validateSubmission(?string $extension, ?string $type, ?string $code, ?string $name, ?Attribute $attribute = NULL): array
    {
        $errors = array();

        try{Attribute::validateExtension($extension);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        try{Attribute::validateType($type);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        if($attribute === NULL OR $attribute->getCode() != $code)
        {
            try{Attribute::validateCode($extension, $type, $code);}
            catch(ValidationException $e){$errors[] = $e->getMessage();}
        }

        try{Attribute::validateName($name);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        return $errors;
    }
}