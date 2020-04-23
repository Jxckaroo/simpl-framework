<?php namespace Jxckaroo\Simpl\Configuration;

use Jxckaroo\Simpl\Exceptions\ConfigurationException;
use Jxckaroo\Simpl\Interfaces\ConfigurationInterface;

/**
 * Class Configuration
 * @package Jxckaroo\Simpl\Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * An array of config options
     * @var array $config
     */
    protected $config;

    /**
     * Configuration constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get the request tree => option from the config file
     * @param $tree
     * @param $option
     * @return mixed
     * @throws ConfigurationException
     */
    public function get($tree, $option)
    {
        if (isset($this->config[$tree][$option]))
        {
            return $this->config[$tree][$option];
        }

        throw new ConfigurationException('Config property could not be found [' . $tree . '][' . $option . ']', 200);
    }
}