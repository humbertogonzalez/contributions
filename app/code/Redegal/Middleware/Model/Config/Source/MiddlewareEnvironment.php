<?php

namespace Redegal\Middleware\Model\Config\Source;

class MiddlewareEnvironment implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'test', 'label' => __('Test')],
            ['value' => 'production', 'label' => __('Production')]
        ];
    }
}
