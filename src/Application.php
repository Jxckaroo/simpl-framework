<?php namespace Jxckaroo\Simpl;

use Jxckaroo\Simpl\Configuration\Configuration;
use Jxckaroo\Simpl\Exceptions\ApplicationException;
use Jxckaroo\Simpl\Exceptions\DatabaseException;
use Jxckaroo\Simpl\Interfaces\ApplicationInterface;
use Jxckaroo\Simpl\Template\Template;
use Whoops;

/**
 * Class Application
 * @package Jxckaroo\Simpl
 */
class Application implements ApplicationInterface
{
    /**
     * @var Configuration $configuration
     */
    protected $configuration;

    /**
     * @var Router\Router $router
     */
    public $router;

    /**
     * The path for the application namespace
     * @var mixed $namespace
     */
    public $namespace;

    /**
     * @var Whoops\Run $whoops
     */
    public $whoops;

    /**
     * Application constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        try {
            // Setup our configuration for application usage
            $this->configuration = new Configuration($config);

            // Attempt to setup the database connection
            Orm::setConnection(
                $this->findDatabase()
            );

            // Instantiate our views
            Template::init(
                $this->configuration->get(
                    'paths',
                    'views'
                ),
                $this->configuration->get(
                    'paths',
                    'views_cache'
                ),
                $this->configuration->get(
                    'application',
                    'cache_views'
                )
            );

            $this->router = new Router\Router(
                $this->configuration->get(
                    'application',
                    'namespace'
                )
            );

            $this->namespace = $this->configuration->get('application', 'namespace');

            $this->whoops = new Whoops\Run();
        } catch (\Exception $e)
        {
            throw new ApplicationException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Try to create a database connection with the required credentials
     * @return \PDO
     * @throws \Exception
     */
    public function findDatabase()
    {
        try {
            $pdo = new \PDO(
                sprintf(
                    '%s:dbname=%s;host=%s',
                    $this->configuration->get('database', 'driver'),
                    $this->configuration->get('database', 'database'),
                    $this->configuration->get('database', 'host')
                ),
                $this->configuration->get('database', 'username'),
                $this->configuration->get('database', 'password')
            );
        } catch (\Exception $e)
        {
            throw new DatabaseException($e->getMessage(), $e->getCode());
        }

        return $pdo;
    }

    /**
     * Autoloader for controllers
     * @param $path
     */
    public function loadControllers($path)
    {
        spl_autoload_register(function ($class) use ($path) {
            $root = $path;   // get the parent directory
            $file = $root . '/' . str_replace('\\', '/', $class) . '.php';
            if (is_readable($file)) {
                require $root . '/' . str_replace('\\', '/', $class) . '.php';
            }
        });
    }

    /**
     * Autoloader for models
     * @param $path
     */
    public function loadModels($path)
    {
        spl_autoload_register(function ($class) use ($path) {
            $root = $path;   // get the parent directory
            $file = $root . '/' . str_replace('\\', '/', $class) . '.php';
            if (is_readable($file)) {
                require $root . '/' . str_replace('\\', '/', $class) . '.php';
            }
        });
    }

    /**
     * Debug function to show current active routes.
     */
    public function seeAllRoutes()
    {
        echo "<pre>";
        print_r($this->router->getRoutes());
        echo "</pre>";
        exit;
    }

    /**
     * @param $exception
     * @return mixed|void
     */
    public function exceptionHandler()
    {
        if ($this->configuration->get('application', 'environment') == 'development') {
            $this->whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $this->whoops->register();
        } else {
            error_reporting(0);
        }
    }

    /**
     * Run the application
     */
    public function run()
    {
        $this->exceptionHandler();

        $this->loadControllers(
            $this->configuration->get(
                'paths',
                'controllers'
            )
        );
        $this->loadModels(
            $this->configuration->get(
                'paths',
                'models'
            )
        );

        $this->router->dispatch(
            $_SERVER['QUERY_STRING']
        );
    }
}