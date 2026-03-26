<?php

namespace Redegal\Middleware\Model\Client\Rest;

use Redegal\Middleware\Model\Client\Request\Request;

abstract class RestRequest extends Request implements \JsonSerializable
{
    const QUERY_TAG = 'query';
    const BODY_TAG = 'body';
    const HEADERS_TAG = 'headers';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';

    protected $body = [];
    protected $query = [];

    public function getType(): string
    {
        return $this::TYPE_REST;
    }

    public function getMethod(): string
    {
        return $this::METHOD_GET;
    }

    public function setData($data)
    {
        $data = parent::setData($data);
        $this->query = $data[self::QUERY_TAG] ?? [];
        $this->body = $data[self::BODY_TAG] ?? [];
        return $data;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function __toArray(): array
    {
        return $this->getBody();
    }
}