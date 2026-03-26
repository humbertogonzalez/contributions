<?php

namespace Redegal\Middleware\Model\Client\Rest;

use Redegal\Middleware\Model\Client\Request\Request;
use Psr\Log\LoggerInterface;

class RestClientFactory
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getClient(?Request $request, array $options = [])
    {
        $client = new RestClient($options, $this->logger);
        return $client;
    }
}
