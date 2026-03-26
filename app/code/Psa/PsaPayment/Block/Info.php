<?php

namespace BalloonGroup\PsaPayment\Block;

use Magento\Framework\Phrase;

/**
 * Class Info - Brief description of class objective
 * @package  code\BalloonGroup\PsaPayment\Block
 */
class Info extends \Magento\Payment\Block\ConfigurableInfo
{
    /**
     * Returns label
     *
     * @param string $field
     * @return Phrase
     */
    protected function getLabel($field)
    {
        return __($field);
    }
}
