<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
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
