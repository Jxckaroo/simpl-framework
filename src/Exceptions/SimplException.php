<?php namespace Jxckaroo\Simpl\Exceptions;

use Throwable;

/**
 * Class SimplException
 * @package Jxckaroo\Simpl\Exceptions
 */
class SimplException extends \Exception
{
    /**
     * SimplException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}