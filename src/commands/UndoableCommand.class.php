<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
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