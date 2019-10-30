<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/02/2019
 * Time: 9:01 PM
 */


namespace extensions\itsm\business;


use business\Operator;
use extensions\itsm\database\URLAliasDatabaseHandler;
use exceptions\ValidationException;
use extensions\itsm\models\URLAlias;
use utilities\HistoryRecorder;

class URLAliasOperator extends Operator
{
    /**
     * @param string $alias
     * @param string $destination
     * @param array $disabled
     * @return URLAlias[]
     * @throws \exceptions\DatabaseException
     */
    public static function search(string $alias = '%', string $destination = '%', $disabled = array()): array
    {
        return URLAliasDatabaseHandler::select($alias, $destination, $disabled);
    }

    /**
     * @param int $param
     * @return URLAlias
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     */
    public static function getURLAlias(int $param): URLAlias
    {
        return URLAliasDatabaseHandler::selectById((int) $param);
    }

    /**
     * @param string|null $alias
     * @param string|null $destination
     * @param string|null $disabled
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function createURLAlias(?string $alias, ?string $destination, ?string $disabled): array
    {
        $errors = self::validateSubmission($alias, $destination, $disabled);

        if(!empty($errors))
            return array('errors' => $errors);

        $urlAlias = URLAliasDatabaseHandler::insert($alias, $destination, (int)$disabled);

        HistoryRecorder::writeHistory('NIS_URLAlias', HistoryRecorder::CREATE, $urlAlias->getId(), $urlAlias);

        return array('id' => $urlAlias->getId());
    }

    /**
     * @param URLAlias $urlAlias
     * @param string|null $alias
     * @param string|null $destination
     * @param string|null $disabled
     * @return array
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function updateURLAlias(URLAlias $urlAlias, ?string $alias, ?string $destination, ?string $disabled): array
    {
        $errors = self::validateSubmission($alias, $destination, $disabled, $urlAlias);

        if(!empty($errors))
            return array('errors' => $errors);

        HistoryRecorder::writeHistory('NIS_URLAlias', HistoryRecorder::MODIFY, $urlAlias->getId(), $urlAlias, array(
            'alias' => $alias,
            'destination' => $destination,
            'disabled' => $disabled
        ));

        $urlAlias = URLAliasDatabaseHandler::update($urlAlias->getId(), $alias, $destination, (int)$disabled);

        return array('id' => $urlAlias->getId());
    }

    /**
     * @param URLAlias $urlAlias
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\EntryNotFoundException
     * @throws \exceptions\SecurityException
     */
    public static function deleteURLAlias(URLAlias $urlAlias): bool
    {
        HistoryRecorder::writeHistory('NIS_URLAlias', HistoryRecorder::DELETE, $urlAlias->getId(), $urlAlias);

        return URLAliasDatabaseHandler::delete($urlAlias->getId());
    }

    /**
     * @param string|null $alias
     * @param string|null $destination
     * @param string|null $disabled
     * @param URLAlias|null $urlAlias
     * @return array
     * @throws \exceptions\DatabaseException
     */
    private static function validateSubmission(?string $alias, ?string $destination, ?string $disabled, ?URLAlias $urlAlias = NULL): array
    {
        $errors = array();

        // Alias
        if($urlAlias === NULL OR $urlAlias->getAlias() != $alias)
        {
            try{URLAlias::validateAlias($alias);}
            catch(ValidationException $e){$errors[] = $e->getMessage();}
        }

        // Destination
        try{URLAlias::validateDestination($destination);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        // Disabled
        try{URLAlias::validateDisabled($disabled);}
        catch(ValidationException $e){$errors[] = $e->getMessage();}

        return $errors;
    }
}