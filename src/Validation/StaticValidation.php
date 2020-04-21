<?php namespace Jxckaroo\Simpl\Validation;

use Jxckaroo\Simpl\Interfaces\StaticValidationInterface;

/**
 * Class StaticValidation
 * @package Jxckaroo\Simpl\Validation
 */
class StaticValidation implements StaticValidationInterface
{
    /**
     * Run validation on the ORM find command
     * @param $arguments
     * @return bool
     */
    public static function findValidation($arguments)
    {
        if (count($arguments) < 1 || (int) $arguments[0] < 1)
        {
            return false;
        }

        return true;
    }
}