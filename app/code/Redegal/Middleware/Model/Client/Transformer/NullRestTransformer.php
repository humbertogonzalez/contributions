<?php

namespace Redegal\Middleware\Model\Client\Transformer;

class NullRestTransformer
{
    const FIELDS = [];

    public function process(&$response)
    {
        return json_decode($response->getBody(), true);
    }
}
