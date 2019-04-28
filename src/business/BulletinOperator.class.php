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
use models\User;

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
}