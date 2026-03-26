<?php

namespace Redegal\Middleware\Model\Client\Rest;

use \GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\HandlerStack;
use Psr\Cache\CacheItemPoolInterface;
use Redegal\Middleware\Model\Client\Rest\Middleware\Logger\ResponseMiddlewareLogger;
use Psr\Log\LoggerInterface;
use kamermans\OAuth2\GrantType\PasswordCredentials;
use kamermans\OAuth2\OAuth2Middleware;

class RestClient
{
    private $options;
    private $client;
    private $logger;

    const TRACE_KEY = 'trace';
    const TRACE_FOLDER_KEY = 'traceFolder';
    const FILE_ID = 'fileId';

    public function __construct(array $options = [], LoggerInterface $logger = null)
    {
        $this->options = $options;
        $this->logger = $logger;
    }

    private function getClient()
    {
        if (null == $this->client) {
            $stack = $this->options['handler'];
            $this->setTrace($stack);
            $this->options['handler'] = $stack;
            $this->client = new Client($this->options);
        }

        return $this->client;
    }

    private function setTrace($stack)
    {
        $trace = $this->options[$this::TRACE_KEY] ?? false;
        $traceFolder = $this->options[$this::TRACE_FOLDER_KEY] ?? '';
        $fileId = $this->options[$this::FILE_ID] ?? null;
        $stack->push(new ResponseMiddlewareLogger($this->logger, $trace, $traceFolder, $fileId), 'logger');
        unset($this->options[$this::TRACE_KEY]);
        unset($this->options[$this::TRACE_FOLDER_KEY]);
        unset($this->options[$this::FILE_ID]);
    }

    /**
     * Set query
     * TODO: Review encode of query
     * @param array $options
     * @return void
     */
    private function setQuery(array &$options, ?array $query)
    {
        if (is_array($query)) {
            $options[RestRequest::QUERY_TAG] = urldecode(http_build_query($query, null, '&', PHP_QUERY_RFC3986));
        }
    }

    public function send(RestRequest $request)
    {
        $client = $this->getClient();
        $options = array_merge($this->options, $request->getOptions());
        $psr7 = new Request($request->getMethod(), $request->getUri(), $request->getHeaders(), json_encode($request->getBody()));
        $query = $request->getQuery() ? ['query' => $request->getQuery()] : [];
        try {
            $response = $client->send($psr7, $query);
            $this->logger->info("[REST] Response: ".json_encode($response, JSON_PRETTY_PRINT));
        } catch (\GuzzleHttp\Exception\ConnectException $ex) {
            $this->logger->critical("[REST] Connection Error", ['exception' => $ex]);
            throw $ex;
        } catch (\GuzzleHttp\Exception\ServerException $ex) {
            $this->logger->critical("[REST] Rest server got an error 500", ['exception' => $ex]);
            throw $ex;
        }

        return $response;
    }
}