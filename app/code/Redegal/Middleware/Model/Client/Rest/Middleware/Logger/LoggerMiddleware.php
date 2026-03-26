<?php

namespace Redegal\Middleware\Model\Client\Rest\Middleware\Logger;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use Redegal\Middleware\Model\Helper\StringHelper;

class LoggerMiddleware
{
    const MAX_PARSE_SIZE = 25;
    private $logger;
    private $logLevel;
    private $errorLogLevel;
    private $trace;
    private $traceFolder;

    public function __construct(
        LoggerInterface $logger,
        $trace,
        $traceFolder = null,
        $logLevel = LogLevel::INFO,
        $errorLogLevel = LogLevel::ERROR
    ) {
        $this->logger = $logger;
        $this->trace = $trace;
        $this->traceFolder = $traceFolder;
        $this->logLevel = $logLevel;
        $this->errorLogLevel = $errorLogLevel;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use (&$handler) {
            return $handler($request, $options)->then(
                function (ResponseInterface $response) use ($request) {
                    $this->log($request, $response);
                    return $response;
                },
                function ($reason) use ($request) {
                    $response = $reason instanceof RequestException
                        ? $reason->getResponse()
                        : null;
                    $this->log($request, $response, $reason);
                    return \GuzzleHttp\Promise\rejection_for($reason);
                }
            );
        };
    }

    private function log(RequestInterface $request, $response, ?\Throwable $reason = null)
    {
        $id = $this->getRandomId();
        $level = $reason ? $this->errorLogLevel : $this->logLevel;
        $service = StringHelper::slug($this->getService($request));
        $file = $this->save($id, $service, $request, $response);
        $exception = $reason ? $reason->getMessage() : null;
        $message = "[REST] $service";
        $this->logger->log($level, $message, [
            'rest' => [
                'meta' => [
                    'id' => $id,
                    'service' => $service,
                    'file' => $file,
                    'cache' => false,
                    'uri' => $request->getRequestTarget(),
                    'method' => $request->getMethod(),
                    'exception' => $exception
                ],
                'request' => $this->getBody($request->getBody()),
                'response' => $response ? $this->getBody($response->getBody()) : [],
            ]
        ]);
    }

    private function getParam($request, $param):string
    {
        parse_str($request->getUri()->getQuery(), $query);
        return $query[$param] ?? '';
    }

    private function getService($request):string
    {
        $service = basename($request->getUri()->getPath());
        $service = StringHelper::kebab($service);
        return $service;
    }

    private function getBody($body)
    {
        $KBytes = round($body->getSize() / 1024, 2);
        if ($KBytes > $this::MAX_PARSE_SIZE) {
            return ["message" => "Too big for parse ($KBytes Kb)", "size" => $KBytes];
        }
        $response =  json_decode($body, true);
        return $response;
    }

    private function getRawBody($message):string
    {
        $body = empty($message) ? '{}' : ltrim((string) $message->getBody());
        if ($body[0] !== '{') { //if is a xml from a error
            $body = '"' . addslashes($body) . '"';
        }
        return $body;
    }

    private function save($id, $service, $request, $response):string
    {
        if (!$this->trace) {
            return '';
        }
        if ($service == 'value') { //not parse sap files like invoices
            return '';
        }
        if (!is_dir($this->traceFolder)) {
            mkdir($this->traceFolder, 0770, true);
        }
        $now = date("Y-m-d-H-i-s");
        $file = join(DIRECTORY_SEPARATOR, [$this->traceFolder,"$now-$id-$service.json"]);
        file_put_contents($file, $this->serialize($request, $response));
        chmod($file, 0660);
        system("jq . $file > $file.tmp && mv $file.tmp $file");
        return $file;
    }

    private function serialize($request, $response)
    {
        $requestBody = $this->getRawBody($request);
        $requestHeaders = $request ? json_encode($request->getHeaders()) : '';
        $responseBody = $this->getRawBody($response);
        $responseHeaders = $response ? json_encode($response->getHeaders()) : '';
        $target = $request->getRequestTarget();
        return <<<JSON
            {
                "request": {
                    "uri": "$target",
                    "body": $requestBody,
                    "headers": $requestHeaders
                },
                "response": {
                    "body": $responseBody,
                    "headers": $responseHeaders
                }
            }
JSON;
    }

    private function getRandomId($size = 10)
    {
        return substr(md5(mt_rand()), 0, $size);
    }
}
