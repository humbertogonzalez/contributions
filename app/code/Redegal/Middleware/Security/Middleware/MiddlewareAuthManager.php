<?php

namespace Redegal\Middleware\Security\Middleware;

use Redegal\Middleware\Repository\TokenMiddlewareRepository;
use kamermans\OAuth2\GrantType\PasswordCredentials;
use kamermans\OAuth2\OAuth2Middleware;
use \GuzzleHttp\HandlerStack;
use Redegal\Middleware\Model\Config\Config;
use \GuzzleHttp\Client;

class MiddlewareAuthManager
{
    private $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    public function getCredentials()
    {
        $credentials = new PasswordCredentials($this->getAuthClient(), $this->getAuthConfig());
        $oauth = new OAuth2Middleware($credentials);
        $stack = HandlerStack::create();
        $stack->push($oauth);

        return $stack;
    }

    public function updateCredentials()
    {
        return $this->getCredentials();
    }

    private function getAuthClient()
    {
        return new Client([
            'base_uri' => $this->getTokenUri()
        ]);
    }

    private function getAuthConfig()
    {
        return [
            "client_id" => $this->getClientId(),
            "client_secret" => $this->getClientSecret(),
            "username" => $this->getUsername(),
            "password" => $this->getPassword(),
            "grant_type" => "password",
            "scope" => "Default_Scope"
        ];
    }
    
    private function getClientId()
    {
        $env = $this->config->get('middleware/environment/env');
        return $this->config->get('middleware/'.$env.'/client_id');
    }

    private function getClientSecret()
    {
        $env = $this->config->get('middleware/environment/env');
        return $this->config->get('middleware/'.$env.'/client_secret');
    }

    private function getUsername()
    {
        $env = $this->config->get('middleware/environment/env');
        return $this->config->get('middleware/'.$env.'/user');
    }

    private function getPassword()
    {
        $env = $this->config->get('middleware/environment/env');
        return $this->config->get('middleware/'.$env.'/password');
    }

    private function getTokenUri()
    {
        $env = $this->config->get('middleware/environment/env');
        return $this->config->get('middleware/'.$env.'/host').$this->config->get('middleware/'.$env.'/token_uri');
    }
}
