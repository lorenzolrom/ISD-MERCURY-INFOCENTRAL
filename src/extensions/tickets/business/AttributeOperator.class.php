<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/13/2019
 * Time: 6:50 PM
 */


namespace extensions\tickets\business;


use business\Operator;
use extensions\tickets\database\AttributeDatabaseHandler;
use exceptions\EntryInUseException;
use exceptions\ValidationError;
use exceptions\ValidationException;
use extensions\tickets\models\Attribute;
use extensions\tickets\models\Workspace;
use utilities\HistoryRecorder;

class AttributeOperator extends Operator
{
    /**
     * @param int $id
     * @return Attribute
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getById(int $id): Attribute
    {
        return AttributeDatabaseHandler::selectById($id);
    }

    /**
     * @param Workspace $workspace
     * @param string $type
     * @param string $code
     * @return Attribute
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getByCode(Workspace $workspace, string $type, string $code): Attribute
    {
        $id = AttributeDatabaseHandler::selectIdByCode($workspace->getId(), $type, $code);

        return AttributeDatabaseHandler::selectById((int)$id);
    }

    /**
     * @param Workspace $workspace
     * @param string $type
     * @return Attribute[]
     * @throws \exceptions\DatabaseException
     */
    public static function getAllOfType(Workspace $workspace, string $type): array
    {
        return AttributeDatabaseHandler::selectByType($workspace->getId(), $type);
    }

    /**
     * @param int $id
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function nameFromId(int $id): ?string
    {
        return AttributeDatabaseHandler::selectNameById($id);
    }

    /**
     * @param int|null $id
     * @return string|null
     * @throws \exceptions\DatabaseException
     */
    public static function codeFromId(?int $id): ?string
    {
        return AttributeDatabaseHandler::selectCodeById((int)$id);
    }

    /**
     * @param Workspace $workspace
     * @param array $vals
     * @return int
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function create(Workspace $workspace, array $vals): int
    {
        self::validate('extensions\tickets\models\Attribute', $vals, TRUE);

        try
        {
            Attribute::__validateCode($workspace->getId(), $vals['type'], $vals['code']);
        }
        catch(ValidationException $e)
        {
            throw new ValidationError(array($e->getMessage()));
        }

        $attr = AttributeDatabaseHandler::insert($workspace->getId(), $vals['type'], $vals['code'], $vals['name']);
        HistoryRecorder::writeHistory('Tickets_Attribute', HistoryRecorder::CREATE, $attr->getId(), $attr);

        return $attr->getId();
    }

    /**
     * @param Attribute $attr
     * @param array $vals
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function update(Attribute $attr, array $vals): bool
    {
        self::validate('extensions\tickets\models\Attribute', $vals);

        // Check code if it has been changed
        if($attr->getCode() != $vals['code'])
        {
            try
            {
                Attribute::__validateCode($attr->getWorkspace(), $attr->getType(), $vals['code']);
            }
            catch(ValidationException $e)
            {
                throw new ValidationError(array($e->getMessage()));
            }
        }

        HistoryRecorder::writeHistory('Tickets_Attribute', HistoryRecorder::MODIFY, $attr->getId(), $attr, $vals);
        AttributeDatabaseHandler::update($attr->getId(), $vals['code'], $vals['name']);

        return TRUE;
    }

    /**
     * @param Attribute $attr
     * @return bool
     * @throws EntryInUseException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function delete(Attribute $attr): bool
    {
        if(AttributeDatabaseHandler::attributeInUse($attr->getId()))
            throw new EntryInUseException('Attribute has already been used on a ticket', EntryInUseException::ENTRY_IN_USE);

        HistoryRecorder::writeHistory('Tickets_Attribute', HistoryRecorder::DELETE, $attr->getId(), $attr);
        AttributeDatabaseHandler::delete($attr->getId());

        return TRUE;
    }
}
