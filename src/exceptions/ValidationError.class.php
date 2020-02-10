<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 5/14/2019
 * Time: 4:00 PM
 */


namespace exceptions;


use Throwable;

/**
 * Class ValidationError
 *
 * A way to make it easier to catch validation exceptions
 *
 * @package exceptions
 */
class ValidationError extends MercuryException
{
    private $errors;

    /**
     * ValidationError constructor.
     * @param array $errors
     * @param Throwable|null $previous
     */
    public function __construct(array $errors, Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct('Validation errors are present', 1700, $previous);
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}