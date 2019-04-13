<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/10/2019
 * Time: 9:44 PM
 */


namespace exceptions;


class EntryInUseException extends MercuryException
{
     const ENTRY_IN_USE = 1500;

     const MESSAGES = array(
         self::ENTRY_IN_USE => 'Record is referenced by one or more other records'
     );
}