<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 11/04/2019
 * Time: 11:38 AM
 */


namespace extensions\facilities\business;


use business\Operator;
use database\DatabaseConnection;
use exceptions\DatabaseException;
use exceptions\EntryNotFoundException;
use exceptions\ValidationError;
use extensions\facilities\database\SpaceDatabaseHandler;
use extensions\facilities\database\SpacePointDatabaseHandler;
use extensions\facilities\models\Floorplan;
use extensions\facilities\models\Space;
use utilities\HistoryRecorder;

class SpaceOperator extends Operator
{
    /**
     * @param array $attrs
     * @return Space
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function createSpace(array $attrs): Space
    {
        $errors = array();
        $location = NULL;

        // Validate space exists
        try
        {
            $location = LocationOperator::getLocationByCode((string)$attrs['buildingCode'], (string)$attrs['locationCode']);
        }
        catch(EntryNotFoundException $e)
        {
            throw new ValidationError(['Location not found']);
        }

        // Validate floor exists
        try
        {
            FloorplanOperator::getFloorplan((int)$attrs['floor']);
        }
        catch(EntryNotFoundException $e)
        {
            throw new ValidationError(['Floor not found']);
        }

        // Validate space does not already exist for Location
        try
        {
            SpaceOperator::getSpace($location->getId());
            $errors[] = 'Space already exists for this Location';
        }
        catch(EntryNotFoundException $e){} // Do nothing

        if(!empty($errors))
            throw new ValidationError($errors);

        // Validate other attributes
        self::validate('extensions\facilities\models\Space', $attrs);

        // Create space
        $space = SpaceDatabaseHandler::insert($location->getId(), (int)$attrs['floor'], $attrs['hexColor'], (float)$attrs['area'], $attrs['unit']);
        HistoryRecorder::writeHistory('Facilities_Space', HistoryRecorder::CREATE, $space->getLocation(), $space);

        // Add points, if present
        if(isset($attrs['points']) AND is_array($attrs['points']))
        {
            self::addPoints($space, $attrs['points']);
        }

        return $space;
    }

    /**
     * @param Space $space
     * @param array $attrs
     * @return Space
     * @throws EntryNotFoundException
     * @throws ValidationError
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function updateSpace(Space $space, array $attrs): Space
    {
        self::validate('extensions\facilities\models\Space', $attrs);

        HistoryRecorder::writeHistory('Facilities_Space', HistoryRecorder::MODIFY, $space->getLocation(), $space, $attrs);
        return SpaceDatabaseHandler::update($space->getLocation(), $attrs['hexColor'], $attrs['area'], $attrs['unit']);
    }

    /**
     * @param Space $space
     * @return bool
     * @throws \exceptions\DatabaseException
     */
    public static function deleteSpace(Space $space): bool
    {
        return SpaceDatabaseHandler::delete($space->getLocation());
    }

    /**
     * @param Space $space
     * @param array $coords array(array('pD', 'pR'),...)
     * @return bool
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function addPoints(Space $space, array $coords): bool
    {
        $count = count($coords);
        $success = 0;

        foreach($coords as $coord)
        {
            if(count($coord) !== 2)
                continue;

            $point = SpacePointDatabaseHandler::insert($space->getLocation(), (float)$coord[0], (float)$coord[1]);
            HistoryRecorder::writeHistory('Facilities_SpacePoint', HistoryRecorder::CREATE, $point->getId(), $point);
            $success += 1;
        }

        return $count === $success;
    }

    /**
     * @param array $pointIds
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function removePoints(array $pointIds): bool
    {
        $count = count($pointIds);
        $success = 0;

        foreach($pointIds as $pointId)
        {
            try
            {
                $point = SpacePointDatabaseHandler::selectById((int)$pointId);
                HistoryRecorder::writeHistory('Facilities_SpacePoint', HistoryRecorder::DELETE, $point->getId(), $point);
                SpacePointDatabaseHandler::delete((int)$pointId);
                $success += 1;
            }
            catch(EntryNotFoundException $e){}
        }

        return $count === $success;
    }

    /**
     * @param Space $space
     * @param array $coords
     * @return bool
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function redefinePoints(Space $space, array $coords): bool
    {
        $c = new DatabaseConnection();
        $c->startTransaction();

        $count = count($coords);
        $success = 0;

        try
        {
            // Remove existing points
            foreach($space->getPoints() as $point)
            {
                HistoryRecorder::writeHistory('Facilities_SpacePoint', HistoryRecorder::DELETE, $point->getId(), $point);
                SpacePointDatabaseHandler::delete($point->getId());
            }

            // Add all coords
            foreach($coords as $coord)
            {
                if(count($coord) !== 2)
                    continue;

                $point = SpacePointDatabaseHandler::insert($space->getLocation(), (float)$coord[0], (float)$coord[1]);
                HistoryRecorder::writeHistory('Facilities_SpacePoint', HistoryRecorder::CREATE, $point->getId(), $point);
                $success += 1;
            }
        }
        catch(DatabaseException $e)
        {
            $c->rollback();
            $c->close();
        }

        // If the replacement succeeded, commit
        if($count === $success)
        {
            $c->commit();
        }
        else
        {
            $c->rollback();
        }

        // Return result
        $c->close();
        return $count === $success;
    }

    /**
     * @param int $location
     * @return Space
     * @throws EntryNotFoundException
     * @throws \exceptions\DatabaseException
     */
    public static function getSpace(int $location): Space
    {
        return SpaceDatabaseHandler::select($location);
    }

    /**
     * @param Floorplan $floor
     * @return Space[]
     * @throws \exceptions\DatabaseException
     */
    public static function getSpaceByFloor(Floorplan $floor): array
    {
        return SpaceDatabaseHandler::selectByFloor($floor->getId());
    }

}
