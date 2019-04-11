<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/11/2019
 * Time: 10:15 AM
 */


namespace business\itsm;


use business\Operator;
use database\itsm\AssetDatabaseHandler;
use models\itsm\Asset;

class AssetOperator extends Operator
{
    /**
     * @param int $assetTag
     * @return Asset
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getAsset(int $assetTag): Asset
    {
        return AssetDatabaseHandler::selectByAssetTag($assetTag);
    }
}