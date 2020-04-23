<?php namespace Jxckaroo\Simpl\Router;

use Jxckaroo\Simpl\Exceptions\RouterException;

/**
 * Class Route
 * @package Jxckaroo\Simpl\Router
 */
class Route extends Router
{
    /**
     * Load a new route in to the application
     * @param $route
     * @param array $params
     */
    public static function add($route, $params = [])
    {
        // Convert the route to a regular expression: escape forward slashes
        $route = preg_replace('/\//', '\\/', self::$prefix . $route);

        // Convert variables e.g. {controller}
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);

        // Convert variables with custom regular expressions e.g. {id:\d+}
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

        // Add start and end delimiters, and case insensitive flag
        $route = '/^' . $route . '$/i';

        self::$routes[$route] = $params;
    }

    /**
     * Add a group with an option prefix
     * @param $prefix
     * @param $callback
     * @throws RouterException
     */
    public static function group($prefix, $callback)
    {
        self::$prefix = $prefix . '/';

        if (!is_callable($callback))
        {
            throw new RouterException("Second parameter to group, must be callable.", 200);
        }

        $callback();
    }
}