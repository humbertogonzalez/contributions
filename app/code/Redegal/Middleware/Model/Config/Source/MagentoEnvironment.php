<?php

namespace Redegal\Middleware\Model\Config\Source;

class MagentoEnvironment implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'PRE', 'label' => __('Pre')],
            ['value' => 'QA', 'label' => __('QA')],
            ['value' => 'PROD', 'label' => __('Producción')]
        ];
    }
}
