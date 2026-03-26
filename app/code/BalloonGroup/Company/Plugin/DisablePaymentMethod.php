<?php

declare(strict_types=1);

namespace BalloonGroup\Company\Plugin;

use Magento\Payment\Model\MethodInterface;

class DisablePaymentMethod
{
    /**
     * Before isAvailable plugin for payment methods
     *
     * @param MethodInterface $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsAvailable(MethodInterface $subject, $result)
    {
        $validMethods = ['cashondelivery'];

        if (in_array($subject->getCode(), $validMethods)) {
            return $result;
        }

        return false;
    }
}
