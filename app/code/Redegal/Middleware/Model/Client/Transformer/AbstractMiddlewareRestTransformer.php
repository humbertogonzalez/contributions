<?php

namespace Redegal\Middleware\Model\Client\Transformer;

use Redegal\Middleware\Model\Client\Transformer\AbstractTransformer;

class AbstractMiddlewareRestTransformer extends AbstractTransformer
{
    protected function toArray(&$response)
    {
        return $response = json_decode($response->getBody(), true);
    }
}
