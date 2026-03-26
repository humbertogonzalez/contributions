<?php

namespace Balloon\RestClient\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class StoreCodes implements OptionSourceInterface
{
    const COD_TELSUR = "Telsur";
    const COD_CENTRO_NORTE = "CentroNorte";
    public function toOptionArray() : array
    {
        return [
            ['value' => self::COD_CENTRO_NORTE, 'label' => __('CentroNorte')],
            ['value' => self::COD_TELSUR, 'label' => __('Telsur')]
        ];
    }
}
