<?php

namespace Redegal\Middleware\Model\Client\Transformer;

use Redegal\Middleware\Model\Helper\ArrayHelper;
use Redegal\Middleware\Model\DataObject\DataObject;

abstract class AbstractTransformer implements TransformerInterface
{
    const COMPONENTS = [];
    const FIELDS = [];
    const DEFAULT_DATAOBJECT = 'Redegal\Middleware\Model\DataObject\DataObject';
    const DEFAULT_PROCESS_OPTIONS = [
        'raw' => false
    ];

    protected $options;
    abstract protected function toArray(&$response);

    public function __construct(array $options)
    {
        $this->options = array_merge($this::DEFAULT_PROCESS_OPTIONS, $options);
    }

    public function getOption($option)
    {
        return $this->options[$option] ?? null;
    }

    public function process(&$response)
    {
        $this->toArray($response);
        if ($this->getOption('raw') || empty($response)) {
            return $response;
        }

        $this->transform($response, $this->getOption('hash'), $this->getOption('raw'), $this->getOption('store'));
        return $response;
    }

    protected function transform(&$response, $hash, $raw = false, $store = null)
    {
        if ($raw || empty($response)) {
            return $response;
        }

        if (!ArrayHelper::isMultiArray($response)) {
            $this->transformObj($response);
            $this->setHash($response, $hash);
            $this->afterTransform($response);
            return $response;
        }

        $transformed = [];
        foreach ($response as $element) {
            $this->transformObj($element);
            $transformed[]= $this->setHash($element, $hash);
        }

        $this->afterTransform($transformed);
        return $response = $transformed;
    }

    public function setStore($store)
    {
        $this->store = $store;
    }

    public function transformObj(&$attributes)
    {
        $result = [];
        if (empty($this::FIELDS)) {
            $result = $attributes;
        }

        $this->setFields($result, $attributes, $this::FIELDS);
        $this->execute($result, $attributes, $this::COMPONENTS);
        $dataObject = $this::DEFAULT_DATAOBJECT;
        if ($dataObject) {
            $attributes = new $dataObject($result);
        }
        return $result;
    }

    public function afterTransform(&$result)
    {
        return $result;
    }

    public function setFields(array &$result, &$attributes, array $fields)
    {
        foreach ($fields as $from => $to) {
            $value = $attributes[$from] ?? null;
            if (isset($to['fields']) && isset($value)) {
                $multiple = isset($to['multiple']);
                $values = ($multiple && ArrayHelper::isMultiArray($value))  ? $value : [$value];
                $results = $array = [];
                foreach ($values as $value) {
                    $results[] = $this->setFields($array, $value, $to['fields']);
                }
                $result[$to['property']] = $multiple ? $results : $results[0];
                continue;
            }
            if (isset($to['type'])) {
                $result[$to['property']] = $this->setValue($value, $to);
            }
        }
        return $result;
    }

    public function execute(array &$result, array &$attributes, array $components)
    {
        foreach ($components as $component) {
            $this->$component($result, $attributes);
        }
    }

    protected function setValue($value, array $to)
    {
        $type = $to['type'] ?? null;
        if (!$type || empty($value)) {
            return $value;
        }
        switch ($type) {
            case 'float':
                return floatval($value);
            case 'array':
                return (array) $value;
            default:
                settype($value, $type);
                return $value;
        }
    }

    public function setLowercaseKeys(array &$result, array &$attributes)
    {
        return $result = ArrayHelper::keysToUnderScore($result);
    }

    protected function ensureRequest(&$request, $type = null)
    {
        if (is_array($request)) {
            $request = $this->createRequest($request, $type);
        }
        return $request;
    }

    protected function setHash(&$element, $active)
    {
        if ($active) {
            $element['hash'] = md5(json_encode($element));
        }
        return $element;
    }
}
