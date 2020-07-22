<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
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
