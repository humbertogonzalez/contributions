<?php

namespace Redegal\Middleware\Model\Client\Request;

abstract class Request
{
    const DEFAULT = [];
    const OPTIONS_TAG = 'request_options';
    const HEADER_TAG = 'headers';
    const TYPE_REST = 'rest';

    protected $options = [];
    protected $headers = [];
    protected $config;

    abstract public function getType(): string;
    abstract public function __toArray(): array;

    public function __construct(array $data, \Redegal\Middleware\Model\Config\Config $config)
    {
        $this->config = $config;
        $this->setData($data);
    }

    public function getOption($option)
    {
        return $this->options[$option] ?? null;
    }

    public function setData($data)
    {
        if (is_array($data)) {
            $data = array_replace_recursive($this::DEFAULT, $data);
            $data = $this->parse($data);
            $this->headers = $data[self::HEADER_TAG] ?? [];
            $this->options = $data[self::OPTIONS_TAG] ?? [];
        }
        return $data;
    }

    public function parse($params)
    {
        if (null == $this->config) {
            return $params;
        }

        return $this->config->parse($params);
    }

    public function setOption($option, $value, $soft = false)
    {
        if (array_key_exists($option, $this->options) && $soft) {
            return $this->options[$option];
        }
        $this->options[$option] = $value;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function __toString()
    {
        return json_encode($this->__toArray());
    }

    public function jsonSerialize()
    {
        return $this->__toArray();
    }
}