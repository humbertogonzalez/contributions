<?php

declare(strict_types=1);

namespace Psa\Checkout\Plugin\Checkout;

use Magento\Checkout\Block\Checkout\AttributeMerger;

class AttributeMergerPlugin
{
    /**
     * @param AttributeMerger $subject
     * @param array $result
     * @param array $elements
     * @param string $providerName
     * @param string $dataScopePrefix
     * @param array $fields
     * @return array
     */
    public function afterMerge(AttributeMerger $subject, array $result, $elements, $providerName, $dataScopePrefix, array $fields = []): array
    {
        if(isset($result['street'])) {
            $result['street']["children"][0]["label"] = __('Calle Y Número');
            $result['street']["children"][0]["placeholder"] = __('Calle Y Número');
            $result['street']["children"][1]["label"] = __('Calle Y Número');
            $result['street']["children"][1]["placeholder"] = __('Barrio');
        }

        return $result;
    }

}
