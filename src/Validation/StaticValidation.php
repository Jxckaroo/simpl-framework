<?php namespace Jxckaroo\Simpl\Validation;

use Jxckaroo\Simpl\Interfaces\StaticValidationInterface;

/**
 * Class MagicMethods
 * @package Jxckaroo\Simpl\Extra
 */
class StaticValidation implements StaticValidationInterface
{
    /**
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