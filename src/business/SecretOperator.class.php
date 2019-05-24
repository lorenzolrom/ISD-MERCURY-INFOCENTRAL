<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 9:28 AM
 */


namespace business;


use database\SecretDatabaseHandler;
use models\Secret;
use utilities\HistoryRecorder;

class SecretOperator extends Operator
{
    /**
     * @param string $secret
     * @return Secret
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getSecret(string $secret): Secret
    {
        return SecretDatabaseHandler::selectBySecret($secret);
    }

    /**
     * @param int $id
     * @return Secret
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getSecretById(int $id): Secret
    {
        return SecretDatabaseHandler::selectById($id);
    }

    /**
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function issue(array $vals): array
    {
        // New secret token
        $secret = hash('SHA512', openssl_random_pseudo_bytes(2048));

        self::validateSecret($vals);

        $secret = SecretDatabaseHandler::insert($secret, $vals['name']);

        $history = HistoryRecorder::writeHistory('Secret', HistoryRecorder::CREATE, $secret->getId(), $secret);

        if(is_array($vals['permissions']))
        {
            HistoryRecorder::writeAssocHistory($history, array('permissions' => $vals['permissions']));
            SecretDatabaseHandler::setPermissions($secret->getSecret(), $vals['permissions']);
        }

        return array('id' => $secret->getId());
    }

    /**
     * @param Secret $secret
     * @param array $vals
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     * @throws \exceptions\ValidationError
     */
    public static function update(Secret $secret, array $vals): array
    {
        self::validateSecret($vals, $secret);

        $history = HistoryRecorder::writeHistory('Secret', HistoryRecorder::MODIFY, $secret->getId(), $secret, $vals);
        SecretDatabaseHandler::update($secret->getId(), $vals['name']);

        if(is_array($vals['permissions']))
        {
            HistoryRecorder::writeAssocHistory($history, array('permissions' => $vals['permissions']));
            SecretDatabaseHandler::setPermissions($secret->getSecret(), $vals['permissions']);
        }

        return array('id' => $secret->getId());
    }

    /**
     * @return Secret[]
     * @throws \exceptions\DatabaseException
     */
    public static function getAll(): array
    {
        return SecretDatabaseHandler::select();
    }

    /**
     * @param Secret $secret
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function delete(Secret $secret): bool
    {
        HistoryRecorder::writeHistory('Secret', HistoryRecorder::DELETE, $secret->getId(), $secret);
        return SecretDatabaseHandler::delete($secret->getId());
    }

    /**
     * @param array $vals
     * @param Secret|null $secret
     * @return bool
     * @throws \exceptions\ValidationError
     */
    private static function validateSecret(array $vals, ?Secret $secret = NULL): bool
    {
        if($secret === NULL OR $secret->getName() != $vals['name'])
        {
            return self::validate('models\Secret', $vals);
        }

        return TRUE;
    }
}