<?php namespace Jxckaroo\Simpl\Template;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Template
{
    protected static $view_namespace;
    protected static $views_cache;

    /**
     * @var $engine Environment
     */
    protected static $engine;

    /**
     * @var $loader FilesystemLoader
     */
    protected static $loader;

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

    public static function build($template, $binds = [])
    {
        echo self::$engine->render($template, $binds);
    }
}