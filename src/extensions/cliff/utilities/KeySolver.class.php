<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/11/2020
 * Time: 9:57 PM
 */


namespace extensions\cliff\utilities;

use exceptions\EntryNotFoundException;
use extensions\cliff\business\CoreOperator;
use extensions\cliff\models\Core;

/**
 * Class KeySolver
 * @package extensions\cliff\utilities
 *
 * Core operation functions from the original CLIFF application.
 *
 * @author Brett T Johnson
 * @author Lorenzo L Romero
 */
class KeySolver
{
    /**
     * Get a vertical stack from a 2D-array core
     * @param array $core 2D array of core
     * @param int $index Index of the pin stack to get
     * @return array Array of pins in the specified stack index
     */
    function getPinStack(array $core, int $index): array
    {
        $stack = array();

        foreach($core as $row)
        {
            $stack[] = $row[$index]; // Get the $index'th pin from each row
        }

        return $stack;
    }

    /**
     * @param array $stack
     * @return array
     */
    function getWorkingCuts(array $stack)
    {
        $workingCuts = array();
        $stackLength = sizeof($stack);

        for($i = 0; $i < $stackLength; $i++)
        {
            $cut = 0;

            for($j = 0; $j <= $i; $j++)
            {
                $cut += (int)$stack[$stackLength - 1 - $j];
            }

            if($cut <= 9 AND !(in_array($cut, $workingCuts)))
            {
                $workingCuts[] = $cut;
            }
        }

        return $workingCuts;
    }

    /**
     * @param array $core 2D array of core pins
     * @return array
     */
    function getWorkingKeys(array $core)
    {
        $stackCount = sizeof($core[0]); // Get the number of pins based on the length of the first set

        $allWorkingCuts = array(); // Store all working keys

        // Get all working key cuts
        for($i = 0; $i < $stackCount; $i++)
        {
            $allWorkingCuts[] = $this->getWorkingCuts($this->getPinStack($core, $i));
        }

        $bittings = array();

        foreach($allWorkingCuts[0] as $cut0)
            foreach($allWorkingCuts[1] as $cut1)
                foreach($allWorkingCuts[2] as $cut2)
                    foreach($allWorkingCuts[3] as $cut3)
                        foreach($allWorkingCuts[4] as $cut4)
                            foreach($allWorkingCuts[5] as $cut5)
                                foreach($allWorkingCuts[6] as $cut6)
                                {
                                    if(!in_array([$cut0, $cut1, $cut2, $cut3, $cut4, $cut5, $cut6], $bittings))
                                        $bittings[] = [$cut0, $cut1, $cut2, $cut3, $cut4, $cut5, $cut6];
                                }

        return $bittings;
    }

    /**
     * @param array $core
     * @return array
     */
    function getControlBitting(array $core): array
    {
        $controlBitting = array();
        $controlRow = $core[0];
        for($i = 0; $i < sizeof($controlRow); $i++)
        {
            $controlBit = 13 - $controlRow[$i];

            if($controlBit > 9)
                $controlBitting[] = '?';
            else
                $controlBitting[] = $controlBit;
        }

        return $controlBitting;
    }

    /**
     * @param int $id
     * @return array
     * @throws \exceptions\DatabaseException
     */
    function getCore(int $id): array
    {
        try
        {
            $core = CoreOperator::get($id);
            $pins = $core->getPinData();

            if(strlen($pins) === 0)
                return array();

            $rawLines = explode("|", $pins);
            $core = array();

            foreach($rawLines as $line)
            {
                $core[] = str_getcsv($line);
            }

            return $core;
        }
        catch(EntryNotFoundException $e)
        {
            return array();
        }
    }

    /**
     * Convert PinData string into array used by KeySolver
     * @param Core $core
     * @return array
     */
    function coreToKSCore(Core $core): array
    {
        $ksCore = array();

        $rawLines = explode('|', $core->getPinData());

        foreach($rawLines as $line)
        {
            $ksCore[] = str_getcsv($line);
        }

        return $ksCore;
    }

    function getTipToBowString(array $bitting): string
    {
        $str = implode('',  $bitting);
        return $str;
    }

    /**
     * @param Core[] $cores
     * @return array Bitting array
     */
    function getCommon(array $cores): ?array
    {
        $keys = array();
        $keyStrings = array();

        foreach($cores as $core)
        {
            $keys = array_merge($keys, $this->getWorkingKeys($this->coreToKSCore($core)));
        }

        foreach($keys as $key)
        {
            $keyStrings[] = $this->getTipToBowString($key);
        }

        $temp = array();

        foreach($keyStrings as $keyString)
        {
            if(!isset($temp[$keyString]))
                $temp[$keyString] = 1;
            else
                $temp[$keyString] = $temp[$keyString] + 1;
        } // This loop ends with the list of all unique keys and the count of their frequency

        $commonKeys = array();
        $numCores = sizeof($cores);

        foreach(array_keys($temp) as $bitting)
        {
            if($temp[$bitting] >= $numCores)
                $commonKeys[] = $bitting;
        }

        return $commonKeys;
    }
}