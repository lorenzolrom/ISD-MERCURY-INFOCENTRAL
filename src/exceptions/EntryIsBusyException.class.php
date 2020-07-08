<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 12/06/2019
 * Time: 10:47 AM
 */


namespace exceptions;


class EntryIsBusyException extends MercuryException
{
    const ENTRY_IS_BUSY = 1800;

    const MESSAGES = array(
        self::ENTRY_IS_BUSY => 'Record is currently in use by another process'
    );
}
