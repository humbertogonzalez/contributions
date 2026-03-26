<?php

namespace Redegal\Middleware\Model\Repository\Factory;

use Redegal\Middleware\Model\Config\Config;

class MiddlewareRequestFactory
{
    protected $config;
    protected $middlewareBaseUrl;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
        $this->middlewareBaseUrl = $this->getBaseMiddlewareUrl();
    }

    public function getRequest($class, array $data = [])
    {
        return new $class($data, $this->config, $this->middlewareBaseUrl);
    }

    private function getBaseMiddlewareUrl()
    {
        $env = $this->config->get('middleware/environment/env');
        return $this->config->get('middleware/'.$env.'/host');
    }
}