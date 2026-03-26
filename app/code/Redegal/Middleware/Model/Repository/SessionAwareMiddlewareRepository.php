<?php

namespace Redegal\Middleware\Model\Repository;

use Redegal\Middleware\Model\Repository\Factory\MiddlewareClientFactory;
use Redegal\Middleware\Model\Repository\Factory\MiddlewareRequestFactory;
use Redegal\Middleware\Model\Repository\Factory\MiddlewareTransformerFactory;
use Redegal\Middleware\Security\Middleware\MiddlewareAuthManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Exception\ClientException;

class SessionAwareMiddlewareRepository extends MiddlewareRepository
{
    protected $authManager;

    public function __construct(
        LoggerInterface $logger,
        MiddlewareClientFactory $clientFactory,
        MiddlewareRequestFactory $requestFactory,
        MiddlewareTransformerFactory $transformerFactory,
        MiddlewareAuthManager $authManager,
        array $params = []
    ) {
        $this->authManager = $authManager;
        parent::__construct($logger, $clientFactory, $requestFactory, $transformerFactory, $params);
    }

    protected function shouldUpdateCredentials(\Exception $exception)
    {
        return $exception instanceof ClientException &&
            in_array($exception->getCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN]);
    }

    protected function getCredentialsHeaders()
    {
        $credentials = $this->authManager->getCredentials();
        return [
            'handler' => $credentials,
            'auth' => 'oauth'
        ];
    }
}
