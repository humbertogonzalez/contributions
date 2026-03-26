<?php

/**
 * Interface for sync adapters
 */
namespace Redegal\Middleware\Model\Client\Transformer;

interface TransformerInterface
{
    public function process(&$attributes);
}
