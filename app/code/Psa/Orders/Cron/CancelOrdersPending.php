<?php

/**
 * @category   BalloonGroup
 * @package    BalloonGroup_Orders
 * @subpackage Cron
 * @version    1.0.0
 */

namespace BalloonGroup\Orders\Cron;

use BalloonGroup\Orders\Setup\Patch\Data\AddNewOrderStatusPatch;
use BalloonGroup\PsaPayment\Model\Payment;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

/**
 * Class CancelOrdersPending - Cancel orders pending
 * @package  BalloonGroup\Orders\Cron
 */
class CancelOrdersPending
{
    private OrderRepositoryInterface $orderRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * CancelOrdersPending constructor
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Cronjob Description
     *
     * @return void
     */
    public function execute(): void
    {
        $filter = $this->searchCriteriaBuilder
            ->addFilter('status', 'pending')
            ->addFilter('created_at', date('Y-m-d H:i:s', strtotime('-2 hours')), 'lt')
            ->create();

        $orders = $this->orderRepository->getList($filter)->getItems();

        foreach ($orders as $order) {
            if ($order->getPayment()->getMethod() == Payment::CODE) {
                $order->cancel();
                $order->setStatus(AddNewOrderStatusPatch::STATUS_PAY_CANCEL_CODE);
                $order->setState(Order::STATE_CANCELED);
                $this->orderRepository->save($order);
            }
        }
    }
}
