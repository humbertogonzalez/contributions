<?php

declare(strict_types=1);

namespace BalloonGroup\UpdateOrders\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class SaveCustomAttributes
{
    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes) {
            $extensionAttributes->setData('sap_invoice', $order->getData('sap_invoice'));
            $extensionAttributes->setData('sap_invoice_file', $order->getData('sap_invoice_file'));
        }
        return $order;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterGetList(OrderRepositoryInterface $subject, $result)
    {
        foreach ($result->getItems() as $order) {
            $this->afterGet($subject, $order);
        }
        return $result;
    }

}

