<?php

namespace Redegal\Middleware\Model\Repository\Factory;

class MiddlewareTransformerFactory
{
    public function getTransformer($class, $options = [])
    {
        return new $class($options);
    }
}
