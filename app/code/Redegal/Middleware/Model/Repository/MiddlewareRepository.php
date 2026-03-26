<?php

namespace Redegal\Middleware\Model\Repository;

use Redegal\Middleware\Model\Client\Request\Request;
use Redegal\Middleware\Exception\NotImplementedException;
use Redegal\Middleware\Model\Repository\RepositoryInterface;
use Redegal\Middleware\Model\Repository\Factory\MiddlewareClientFactory;
use Redegal\Middleware\Model\Repository\Factory\MiddlewareRequestFactory;
use Redegal\Middleware\Model\Repository\Factory\MiddlewareTransformerFactory;
use Redegal\Middleware\Model\Client\Transformer\NullRestTransformer;
use Psr\Log\LoggerInterface;

abstract class MiddlewareRepository implements RepositoryInterface
{
    const DEFAULT_CHUNK_SIZE = 1000;
    const MAX_TRIES = 3;

    protected $logger;
    protected $clientFactory;
    protected $requestFactory;
    protected $transformerFactory;
    protected $raw;
    protected $tries = 0;

    public function __construct(
        LoggerInterface $logger,
        MiddlewareClientFactory $clientFactory,
        MiddlewareRequestFactory $requestFactory,
        MiddlewareTransformerFactory $transformerFactory,
        array $params = []
    ) {
        $this->logger = $logger;
        $this->clientFactory = $clientFactory;
        $this->requestFactory = $requestFactory;
        $this->transformerFactory = $transformerFactory;
        $this->raw = $params['raw'] ?? false;
    }

    public function getDefaultRequestOptions()
    {
        return [
            'headers' =>  [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ];
    }

    public function getClient(Request $request, array $clientOptions = [])
    {
        return $this->clientFactory->getClient($request, $clientOptions);
    }

    public function getRequest($class, array $data = []): Request
    {
        return $this->requestFactory->getRequest($class, array_replace_recursive($this->getDefaultRequestOptions(), $data));
    }

    public function getTransformer($class)
    {
        return $this->transformerFactory->getTransformer($class, ['raw' => $this->isRaw()]);
    }

    public function invoke($params, string $requestClass, string $transformerClass = null)
    {
        $request = $this->getRequest($requestClass, $params);
        $transformer = is_null($transformerClass)
            ? null
            : ($this->isRaw() ? new NullRestTransformer() : $this->getTransformer($transformerClass));
        $client = $this->getClient($request, $params['client_options'] ?? []);
        $response = $client->send($request);
        return is_null($transformer) ? $response : $transformer->process($response);
    }

    public function setRaw(bool $raw)
    {
        $this->raw = $raw;
        return $this;
    }

    public function find($id)
    {
        throw new NotImplementedException('Not implemented');
    }

    public function findAll()
    {
        return $this->findBy([]);
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
    {
        throw new NotImplementedException('Not implemented');
    }

    public function findOneBy(array $criteria)
    {
        return $this->findBy($criteria, null, 1);
    }

    public function isRaw()
    {
        return $this->raw;
    }

    public function findBulk(array $criteria, ?array $orderBy = null, $chunkSize = null, $indexBy = null, $limit = null)
    {
        throw new NotImplementedException('Not implemented');
    }
}
