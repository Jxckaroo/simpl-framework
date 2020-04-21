<?php namespace Jxckaroo\Simpl\Template;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Class Template
 * @package Jxckaroo\Simpl\Template
 */
class Template
{
    /**
     * The path for the application views
     * @var string $view_namespace
     */
    protected static $view_namespace;

    /**
     * The path for the cached application views
     * @var string $views_cache
     */
    protected static $views_cache;

    /**
     * @var $engine Environment
     */
    protected static $engine;

    /**
     * @var $loader FilesystemLoader
     */
    protected static $loader;

    /**
     * Initialise the template engine
     * @param $view_namespace
     * @param $views_cache
     * @param bool $views_cachable
     */
    public static function init($view_namespace, $views_cache, $views_cachable = false)
    {
        self::$view_namespace = $view_namespace;
        self::$views_cache = $views_cache;
        self::$loader = new FilesystemLoader($view_namespace);

        if ($views_cachable == true)
        {
            self::$engine = new Environment(self::$loader, [
                'cache' => $views_cache
            ]);
        } else {
            self::$engine = new Environment(self::$loader);
        }

    }

    /**
     * Attempt to server the selected template file.
     * @param $template
     * @param array $binds
     */
    public static function build($template, $binds = [])
    {
        echo self::$engine->render($template, $binds);
    }
}