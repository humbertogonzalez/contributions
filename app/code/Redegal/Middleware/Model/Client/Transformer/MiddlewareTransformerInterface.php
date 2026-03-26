<?php
namespace Redegal\Middleware\Model\Client\Transformer;

interface MiddlewareTransformerInterface
{
    public function transform(iterable $response): iterable;

}
