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
 * Time: 10:51 AM
 */


namespace extensions\tickets\business;


use business\Operator;
use controllers\CurrentUserController;
use exceptions\EntryIsBusyException;
use extensions\tickets\database\LockDatabaseHandler;
use extensions\tickets\models\Lock;
use extensions\tickets\models\Ticket;

class LockOperator extends Operator
{
    /**
     * @param Ticket $ticket
     *
     * Determines if the provided ticket is locked.  If not all lock records for the ticket are deleted
     *
     * @return Lock|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public static function getActiveLock(Ticket $ticket): ?Lock
    {
        $lock = LockDatabaseHandler::selectActive($ticket->getId());

        // Is the ticket locked, and the locking user NOT the current user?
        if($lock !== NULL AND $lock->getUser() !== CurrentUserController::currentUser()->getId())
            return $lock;

        return NULL;
    }

    /**
     * @param Ticket $ticket
     *
     * Locks a ticket if no lock exists
     * Updates the lock if the requesting user is the user holding the active lock
     * Throws exception if another user is attempting to lock ticket
     *
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     * @throws EntryIsBusyException
     */
    public static function updateLock(Ticket $ticket): bool
    {
        $lock = LockDatabaseHandler::selectActive($ticket->getId());

        if($lock === NULL) // If the ticket is not locked, lock it
        {
            return LockDatabaseHandler::insert($ticket->getId(), CurrentUserController::currentUser()->getId()); // Assign new lock
        }

        if($lock->getUser() === CurrentUserController::currentUser()->getId()) // If this user is the user with the lock, update the entry
            return LockDatabaseHandler::update($ticket->getId());

        // Ticket is attempting to be locked by a user who does not hold the lock on the ticket
        throw new EntryIsBusyException(EntryIsBusyException::MESSAGES[EntryIsBusyException::ENTRY_IS_BUSY],EntryIsBusyException::ENTRY_IS_BUSY);
    }
}
