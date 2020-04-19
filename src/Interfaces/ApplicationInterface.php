<?php

namespace Jxckaroo\Simpl\Interfaces;


/**
 * Class Application
 * @package Jxckaroo\Simpl
 */
interface ApplicationInterface
{
    /**
     * Try to create a database connection with the required credentials
     * @return \PDO
     * @throws \Exception
     */
    public function findDatabase();

    /**
     * Autoloader for controllers
     * @param $path
     */
    public function loadControllers($path);

    /**
     * Autoloader for models
     * @param $path
     */
    public function loadModels($path);

    /**
     * Debug function to show current active routes.
     */
    public function seeAllRoutes();

    /**
     * @param $exception
     * @return mixed
     */
    public function exceptionHandler();

    /**
     * Run the application
     */
    public function run();
}