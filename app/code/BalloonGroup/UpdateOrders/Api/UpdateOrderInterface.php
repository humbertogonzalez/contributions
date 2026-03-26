<?php

declare(strict_types=1);

namespace BalloonGroup\UpdateOrders\Api;

interface UpdateOrderInterface
{
    /**
     * Bulk update order invoice data
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function updateOrders(\Magento\Sales\Api\Data\OrderInterface $order): \Magento\Sales\Api\Data\OrderInterface;
}
