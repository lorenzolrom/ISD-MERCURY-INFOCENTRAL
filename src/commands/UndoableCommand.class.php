<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/technologies
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 12/23/2019
 * Time: 9:55 AM
 */


namespace commands;


interface UndoableCommand extends Command
{
    public function undo();
}
