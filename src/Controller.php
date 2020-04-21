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
     * @var RouteParams $route_params
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