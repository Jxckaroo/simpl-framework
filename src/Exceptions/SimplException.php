<?php


namespace Jxckaroo\Simpl\Exceptions;


use Throwable;

class SimplException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}