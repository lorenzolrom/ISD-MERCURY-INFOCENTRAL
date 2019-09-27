<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/05/2019
 * Time: 9:10 PM
 */


namespace business\itsm;


use business\Operator;
use exceptions\ValidationError;
use exceptions\ValidationException;
use database\itsm\HostCategoryDatabaseHandler;
use models\itsm\HostCategory;
use utilities\HistoryRecorder;

class HostCategoryOperator extends Operator
{
    /**
     * @param int $id
     * @return HostCategory
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getCategory(int $id): HostCategory
    {
        return HostCategoryDatabaseHandler::selectById($id);
    }

    /**
     * @return HostCategory[]
     * @throws \exceptions\DatabaseException
     */
    public static function getAll(): array
    {
        return HostCategoryDatabaseHandler::select();
    }

    /**
     * @return array
     * @throws \exceptions\DatabaseException
     */
    public static function getDisplayed(): array
    {
        return HostCategoryDatabaseHandler::selectDisplayed();
    }

    /**
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function create(array $vals): array
    {
        self::validateCategory($vals);

        $hostCategory = HostCategoryDatabaseHandler::insert($vals['name'], (int)$vals['displayed']);

        $history = HistoryRecorder::writeHistory('ITSM_HostCategory', HistoryRecorder::CREATE, $hostCategory->getId(), $hostCategory);

        if(is_array($vals['hosts']))
        {
            HistoryRecorder::writeAssocHistory($history, array('hosts' => $vals['hosts']));
            HostCategoryDatabaseHandler::setHosts($hostCategory->getId(), $vals['hosts']);
        }

        return array('id' => $hostCategory->getId());
    }

    /**
     * @param HostCategory $category
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function update(HostCategory $category, array $vals): array
    {
        self::validateCategory($vals, $category);

        $history = HistoryRecorder::writeHistory('ITSM_HostCategory', HistoryRecorder::MODIFY, $category->getId(), $category, $vals);

        $category = HostCategoryDatabaseHandler::update($category->getId(), $vals['name'], (int)$vals['displayed']);

        if(is_array($vals['hosts']))
        {
            HistoryRecorder::writeAssocHistory($history, array('hosts' => $vals['hosts']));
            HostCategoryDatabaseHandler::setHosts($category->getId(), $vals['hosts']);
        }

        return array('id' => $category->getId());
    }

    /**
     * @param HostCategory $category
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function delete(HostCategory $category): bool
    {
        HistoryRecorder::writeHistory('ITSM_HostCategory', HistoryRecorder::DELETE, $category->getId(), $category);

        return HostCategoryDatabaseHandler::delete($category->getId());
    }

    /**
     * @param array $vals
     * @param HostCategory|null $category
     * @return bool
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     */
    private static function validateCategory(array $vals, ?HostCategory $category = NULL): bool
    {
        if($category === NULL OR $category->getName() != $vals['name'])
        {
            try{HostCategory::_validateName($vals['name']);}
            catch(ValidationException $e){throw new ValidationError(array($e->getMessage()));}
        }

        self::validate('models\itsm\HostCategory', $vals);

        return TRUE;
    }
}