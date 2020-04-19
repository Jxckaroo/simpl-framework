<?php namespace Jxckaroo\Simpl\Configuration;

use Jxckaroo\Simpl\Exceptions\ConfigurationException;
use Jxckaroo\Simpl\Interfaces\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get($tree, $option)
    {
        if (isset($this->config[$tree][$option]))
        {
            return $this->config[$tree][$option];
        }

        throw new ConfigurationException('Config property could not be found [' . $tree . '][' . $option . ']', 200);
    }
}