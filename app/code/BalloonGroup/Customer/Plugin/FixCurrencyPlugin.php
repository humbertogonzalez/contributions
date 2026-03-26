<?php

declare(strict_types=1);

namespace BalloonGroup\Customer\Plugin;

use Magento\Directory\Model\Currency;

class FixCurrencyPlugin
{
    /**
     * @param Currency $subject
     * @param string $result
     * @param float $price
     * @param array $options
     * @return string
     */
    public function afterFormatTxt(Currency $subject, string $result, $price, $options = []): string
    {
        return str_replace(' ', ' ', $result);;
    }

}
