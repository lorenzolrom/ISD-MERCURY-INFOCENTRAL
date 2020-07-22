<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/12/2020
 * Time: 8:16 AM
 */


namespace extensions\cliff\utilities;

use extensions\cliff\models\Key;

/**
 * Class CoreBuilder
 * @package extensions\cliff\utilities
 *
 * Core assembler code from original CLIFF application
 */
class CoreBuilder
{
    function emptyBottomPin(array $core): bool
    {
        foreach($core as $stack)
        {
            if($stack[sizeof($stack) - 1] === NULL)
                return TRUE;
        }

        return FALSE;
    }

    function getPinDataString(array $core): ?string
    {
        $pinStr = "";

        foreach($core as $row)
        {
            $pinStr .= implode(',', $row) . '|';
        }

        $pinStr = rtrim($pinStr, '|');

        return $pinStr;
    }

    /**
     * @param Key $control Key object that will act as control
     * @param Key[] $operating Array of Key objects that will operate the core
     * @return array|null Segment data or NULL as ARRAY (not string format)
     */
    function buildCore(Key $control, array $operating): ?array
    {
        $ctrlBitting = $control->getBitting();
        $ctrlBitting = str_split($ctrlBitting);

        $opBittings = array();

        foreach($operating as $key)
        {
            $opBittings[] = $key->getBitting();
        }

        $stacks = [array(),array(),array(),array(),array(),array(),array()];

        foreach($opBittings as $bitting)
        {
            $cuts = str_split($bitting);

            for($i = 0; $i < sizeof($cuts); $i++)
            {
                $stacks[$i][] = $cuts[$i];
            }
        }

        $tempCore = [array(), array(), array(), array(), array(), array(), array()];

        for($i = 0; $i < sizeof($stacks); $i++)
        {
            $stack = $stacks[$i];
            $stack[] = $ctrlBitting[$i] + 10;
            $uniqCuts = array();

            foreach($stack as $cut)
            {
                if(!in_array($cut, $uniqCuts))
                    $uniqCuts[] = $cut;
            }

            sort($uniqCuts);

            $tempCore[$i][] = $uniqCuts[0]; // first pin in unique cuts is the bottom pin

            for($j = 0; $j < sizeof($uniqCuts); $j++)
            {
                $cut = 0;

                if(isset($uniqCuts[$j + 1]))
                    $cut = $uniqCuts[$j + 1] - $uniqCuts[$j];

                $tempCore[$i][] = $cut;
            }

            for($k = 0; $k < sizeof($tempCore); $k++)
            {
                $sumStack = 0;

                for($k = 0; $k < sizeof($tempCore[$i]); $k++)
                {
                    $sumStack += $tempCore[$i][$k];
                }

                $tempCore[$i][] = 23 - $sumStack;
            }
        }

        $maxStackHeight = 0;

        for($j = 0; $j < sizeof($tempCore); $j++) // Clear zeroes that are not bottom pins
        {
            $stack = $tempCore[$j];

            for($i = 1; $i < sizeof($stack); $i++)
            {
                if($stack[$i] === 0)
                    unset($tempCore[$j][$i]);
            }

            $tempCore[$j] = array_reverse(array_values($tempCore[$j])); // Reset array indexes and reverse, top->bottom

            if(sizeof($tempCore[$j]) > $maxStackHeight)
                $maxStackHeight = sizeof($tempCore[$j]);
        }

        // Add spacing between control and build-up and remaining pins
        for($i = 0; $i < sizeof($tempCore); $i++)
        {
            if(sizeof($tempCore[$i]) == $maxStackHeight) // Skip if this stack has the same pins as the max #
                continue;

            $diff = $maxStackHeight - sizeof($tempCore[$i]);

            for($j = 0; $j < $diff; $j++) // Add padding
            {
                $tempCore[$i][] = NULL;
            }

            while($this->emptyBottomPin($tempCore))
            {
                for ($j = sizeof($tempCore[$i]) - 1; $j > 2; $j--)
                {
                    if ($tempCore[$i][$j] !== NULL)
                        continue;

                    $tempCore[$i][$j] = $tempCore[$i][$j - 1];
                    $tempCore[$i][$j - 1] = NULL;
                }
            }
        }

        // Rotate the core 90 degrees counter-clockwise
        $finCore = [];

        for($i = 0; $i < $maxStackHeight; $i++)
        {
            $finCore[] = array(NULL, NULL, NULL, NULL, NULL, NULL, NULL);
        }

        for($i = 0; $i < sizeof($tempCore); $i++)
        {
            for($j = 0; $j < sizeof($tempCore[$i]); $j++)
            {
                $finCore[$j][$i] = $tempCore[$i][$j];
            }
        }


        return $finCore;
    }
}
