<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/28/2019
 * Time: 12:41 PM
 */


namespace business;


use database\BulletinDatabaseHandler;
use models\Bulletin;
use models\Role;
use models\User;
use utilities\HistoryRecorder;
use utilities\Validator;

class BulletinOperator extends Operator
{
    /**
     * @param User $user
     * @return Bulletin[]
     * @throws \exceptions\DatabaseException
     */
    public static function getBulletinsByUser(User $user): array
    {
        return BulletinDatabaseHandler::selectActiveByUser($user->getId());
    }

    /**
     * @param int $id
     * @return Bulletin
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getBulletin(int $id): Bulletin
    {
        return BulletinDatabaseHandler::selectById($id);
    }

    /**
     * @param array $args
     * @return Bulletin[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(?array $args = NULL): array
    {
        if($args === NULL)
            return BulletinDatabaseHandler::select();

        if($args['startDate'] === NULL OR !Validator::validDate($args['startDate']))
            $args['startDate'] = '1000-01-01';
        if($args['endDate'] === NULL OR !Validator::validDate($args['endDate']))
            $args['endDate'] = '9999-12-31';
        if($args['title'] === NULL)
            $args['title'] = '%';
        else
            $args['title'] = "%{$args['title']}%";
        if($args['message'] === NULL)
            $args['message'] = '%';
        else
            $args['message'] = "%{$args['message']}%";
        if(!is_array($args['inactive']))
            $args['inactive'] = NULL;

        return BulletinDatabaseHandler::select($args['startDate'], $args['endDate'], $args['title'], $args['message'], $args['inactive']);
    }

    /**
     * @param Bulletin $bulletin
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function deleteBulletin(Bulletin $bulletin): bool
    {
        HistoryRecorder::writeHistory('Bulletin', HistoryRecorder::DELETE, $bulletin->getId(), $bulletin);

        return BulletinDatabaseHandler::delete($bulletin->getId());
    }

    /**
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function createBulletin(array $vals): array
    {
        self::validate('models\Bulletin', $vals);

        $vals['inactive'] = (int)$vals['inactive'];

        if($vals['endDate'] === NULL OR strlen($vals['endDate']) === 0)
            $vals['endDate'] = '9999-12-31';

        $bulletin = BulletinDatabaseHandler::insert($vals['startDate'], $vals['endDate'], $vals['title'], $vals['message'], $vals['inactive'], $vals['type']);

        $history = HistoryRecorder::writeHistory('Bulletin', HistoryRecorder::CREATE, $bulletin->getId(), $bulletin);

        if(is_array($vals['roles']))
        {
            BulletinDatabaseHandler::setRoles($bulletin->getId(), $vals['roles']);
            HistoryRecorder::writeAssocHistory($history, array('roles' => $vals['roles']));
        }

        return array('id' => $bulletin->getId());
    }

    /**
     * @param Bulletin $bulletin
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function updateBulletin(Bulletin $bulletin, array $vals): array
    {
        self::validate('models\Bulletin', $vals);

        $vals['inactive'] = (int)$vals['inactive'];

        if($vals['endDate'] === NULL OR strlen($vals['endDate']) === 0)
            $vals['endDate'] = '9999-12-31';

        $history = HistoryRecorder::writeHistory('Bulletin', HistoryRecorder::MODIFY, $bulletin->getId(), $bulletin, $vals);

        if(is_array($vals['roles']))
        {
            BulletinDatabaseHandler::setRoles($bulletin->getId(), $vals['roles']);
            HistoryRecorder::writeAssocHistory($history, array('roles' => $vals['roles']));
        }

        $bulletin = BulletinDatabaseHandler::update($bulletin->getId(), $vals['startDate'], $vals['endDate'], $vals['title'], $vals['message'], $vals['inactive'], $vals['type']);

        return array('id' => $bulletin->getId());
    }

    /**
     * @param Bulletin $bulletin
     * @return Role[]
     * @throws \exceptions\DatabaseException
     */
    public static function getRoles(Bulletin $bulletin): array
    {
        return BulletinDatabaseHandler::getRoles($bulletin->getId());
    }
}