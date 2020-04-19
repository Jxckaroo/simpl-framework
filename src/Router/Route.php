<?php


namespace Jxckaroo\Simpl\Router;


class Route extends Router
{
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

    public static function group($prefix, $callback)
    {
        self::$prefix = $prefix . '/';
        $callback();
    }
}