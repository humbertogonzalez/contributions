<?php

declare(strict_types=1);

namespace BalloonGroup\Distributes\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\Pricing\Helper\Data;

class Order extends AbstractHelper
{
    /**
     * Order constructor
     *
     * @param OrderRepository $orderRepository
     * @param Data $data
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected Data $data
    )
    {

    }

    /**
     * @param $orderId
     * @return OrderInterface
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getOrderData($orderId): OrderInterface
    {
        return $this->orderRepository->get($orderId);
    }

    /**
     * @param $amount
     * @return float|string
     */
    public function getFormattedPrice($amount): float|string
    {
        return $this->data->currency($amount, true, false);
    }
}
