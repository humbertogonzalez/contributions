<?php

namespace Redegal\Middleware\Model\Repository\Factory;

use Redegal\Middleware\Model\Client\Request\Request;
use Redegal\Middleware\Model\Client\Rest\RestClientFactory;
use Psr\Log\LoggerInterface;

/**
 * TODO: Implement other factories like soap if is necessary
 */
class MiddlewareClientFactory
{
    protected $restClientFactory;
    protected $logger;

    public function __construct(LoggerInterface $logger, RestClientFactory $restClientFactory)
    {
        $this->logger = $logger;
        $this->restClientFactory = $restClientFactory;
    }

    public function getClient(Request $request, array $options = [])
    {
        $this->restClientFactory->setLogger($this->logger);
        return $this->restClientFactory->getClient($request, $options);
    }
}
