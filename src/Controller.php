<?php namespace Jxckaroo\Simpl;

use Jxckaroo\Simpl\Interfaces\ControllerInterface;
use Jxckaroo\Simpl\Router\RouteParams;

/**
 * Class Controller
 * @package Jxckaroo\Simpl
 */
class Controller implements ControllerInterface
{
    /**
     * Used to get parameters from chosen route
     * @var RouteParams
     */
    protected $route_params;

    /**
     * Controller constructor.
     * @param RouteParams $route_params
     */
    public function __construct(RouteParams $route_params)
    {
        $this->route_params = $route_params;
    }
}