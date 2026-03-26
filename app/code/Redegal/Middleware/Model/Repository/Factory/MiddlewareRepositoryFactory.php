<?php

namespace Redegal\Middleware\Model\Repository\Factory;

use Redegal\Middleware\Model\Repository\MiddlewareRepository;
use Redegal\Middleware\Model\Repository\SessionAwareMiddlewareRepository;
use Redegal\Middleware\Security\Middleware\MiddlewareAuthManager;
use Redegal\Middleware\Model\Repository\Factory\MiddlewareTransformerFactory;
use Redegal\Middleware\Model\Repository\Factory\MiddlewareRequestFactory;
use Redegal\Middleware\Model\Repository\Factory\MiddlewareClientFactory;
use Psr\Log\LoggerInterface;

class MiddlewareRepositoryFactory
{
    protected $clientFactory;
    protected $requestFactory;
    protected $transformerFactory;
    protected $authManager;


    public function __construct(
        LoggerInterface $logger,
        MiddlewareClientFactory $clientFactory,
        MiddlewareRequestFactory $requestFactory,
        MiddlewareTransformerFactory $transformerFactory,
        MiddlewareAuthManager $authManager
    ) {
        $this->logger = $logger;
        $this->clientFactory = $clientFactory;
        $this->requestFactory = $requestFactory;
        $this->transformerFactory = $transformerFactory;
        $this->authManager = $authManager;
    }

    public function getRepository($class, array $data = []) : MiddlewareRepository
    {
        if (is_subclass_of($class, SessionAwareMiddlewareRepository::class)) {
            return $this->getSessionAwareRepository($class, $data);
        }

        return new $class(
            $this->logger,
            $this->clientFactory,
            $this->requestFactory,
            $this->transformerFactory,
            $data
        );
    }

    private function getSessionAwareRepository($class, $data)
    {
        return new $class(
            $this->logger,
            $this->clientFactory,
            $this->requestFactory,
            $this->transformerFactory,
            $this->authManager,
            $data
        );
    }
}
