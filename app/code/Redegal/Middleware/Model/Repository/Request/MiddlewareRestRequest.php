<?php

namespace Redegal\Middleware\Model\Repository\Request;

use Redegal\Middleware\Model\Client\Rest\RestRequest;
use Redegal\Middleware\Model\Config\Config;

class MiddlewareRestRequest extends RestRequest
{
    const DEFAULT = [];

    protected $baseUrl;

    public function __construct(
        array $data,
        Config $config
    ) {
        $this->config = $config;
        $this->setData($data);
        $this->baseUrl = $this->getBaseMiddlewareUrl();
    }

    private function getBaseMiddlewareUrl()
    {
        $env = $this->config->get('middleware/environment/env');
        return $this->config->get('middleware/'.$env.'/host');
    }
}