<?php

namespace Redegal\Middleware\Model\Repository\Request;

class InventoryBalanceOrderRestRequest extends MiddlewareRestRequest
{
    public function getUri(): string
    {
        return $this->baseUrl .':'.$this->getConfigPort(). $this->getConfigUri();
    }

    public function getMethod(): string
    {
        return $this::METHOD_POST;
    }

    private function getConfigUri()
    {
        $env = $this->config->get('middleware/environment/env');
        return $this->config->get('middleware/'.$env.'/checkout_stock_uri');
    }

    private function getConfigPort()
    {
        $env = $this->config->get('middleware/environment/env');
        return $this->config->get('middleware/'.$env.'/port');
    }
}
