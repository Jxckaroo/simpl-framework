<?php  namespace Jxckaroo\Simpl\Router;

use Jxckaroo\Simpl\Interfaces\RouteParamsInterface;

/**
 * Class RouteParams
 * @package Jxckaroo\Simpl\Router
 */
class RouteParams implements RouteParamsInterface
{
    /**
     * RouteParams constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        if (!empty($params))
        {
            foreach ($params as $key => $value)
            {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Attempt to return a property
     * @param $param_name
     * @return string
     */
    public function get($param_name)
    {
        if (property_exists($this, $param_name))
        {
            return $this->{$param_name};
        }

        return '';
    }
}
