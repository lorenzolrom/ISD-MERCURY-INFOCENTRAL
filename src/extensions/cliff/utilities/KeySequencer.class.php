<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 5/12/2020
 * Time: 1:01 PM
 */


namespace extensions\cliff\utilities;


use extensions\cliff\business\KeyOperator;

class KeySequencer
{
    /**
     * @param string $systemCode
     * @param string $stamp Base stamp for all keys
     * @param string $type
     * @param string $keyway
     * @param string $c1 Chamber 1 valid cuts, comma separated
     * @param string $c2 Chamber 2, etc.
     * @param string $c3
     * @param string $c4
     * @param string $c5
     * @param string $c6
     * @param string $c7
     * @param int $seqStart Beginning change number
     * @param int $seqEnd End change number
     * @param int $padding Number of spaces to pad change number with '0'
     * @return int Number of keys created
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    function sequenceKeys(string $systemCode, string $stamp, string $type, string $keyway, string $c1, string $c2, string $c3, string $c4, string $c5, string $c6, string $c7, int $seqStart = 1, int $seqEnd = 256, int $padding = 2): int
    {
        $keysCreated = 0;

        $c1Array = explode(',', $c1);
        $c2Array = explode(',', $c2);
        $c3Array = explode(',', $c3);
        $c4Array = explode(',', $c4);
        $c5Array = explode(',', $c5);
        $c6Array = explode(',', $c6);
        $c7Array = explode(',', $c7);

        for($c1Index = 0; $c1Index < sizeof($c1Array); $c1Index++)
        {
            for($c2Index = 0; $c2Index < sizeof($c2Array); $c2Index++)
            {
                for($c3Index = 0; $c3Index < sizeof($c3Array); $c3Index++)
                {
                    for($c4Index = 0; $c4Index < sizeof($c4Array); $c4Index++)
                    {
                        for($c5Index = 0; $c5Index < sizeof($c5Array); $c5Index++)
                        {
                            for($c6Index = 0; $c6Index < sizeof($c6Array); $c6Index++)
                            {
                                for($c7Index = 0; $c7Index < sizeof($c7Array); $c7Index++)
                                {

                                    if($seqStart > $seqEnd)
                                        continue;

                                    $keystamp = $stamp . '.' . str_pad($seqStart, $padding, '0', STR_PAD_LEFT);
                                    $keybitting = $c1Array[$c1Index] . $c2Array[$c2Index] . $c3Array[$c3Index] . $c4Array[$c4Index] . $c5Array[$c5Index] . $c6Array[$c6Index] . $c7Array[$c7Index];

                                    KeyOperator::create(array(
                                        'systemCode' => $systemCode,
                                        'stamp' => $keystamp,
                                        'bitting' => $keybitting,
                                        'type' => $type,
                                        'keyway' => $keyway,
                                        'notes' => ''
                                    ));

                                    $keysCreated++;
                                    $seqStart++;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $keysCreated;
    }
}
