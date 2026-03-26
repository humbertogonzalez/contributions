<?php

/**
 * @category   BalloonGroup
 * @package    BalloonGroup_Orders
 * @subpackage Cron
 * @version    1.0.0
 */

namespace BalloonGroup\Orders\Cron;

use BalloonGroup\PsaPayment\Model\Payment;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Convert\Order as OrderConverter;
use Magento\Sales\Model\Service\InvoiceService;
use Psr\Log\LoggerInterface;

class FinishOrdersConfirm
{
    private OrderRepositoryInterface $orderRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private OrderConverter $orderConverter;
    private InvoiceService $invoiceService;
    private Transaction $transaction;
    private LoggerInterface $logger;

    /**
     * FinishOrdersConfirm constructor
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderConverter $orderConverter
     * @param InvoiceService $invoiceService
     * @param Transaction $transaction
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderConverter $orderConverter,
        InvoiceService $invoiceService,
        Transaction $transaction,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderConverter = $orderConverter;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->logger = $logger;
    }

    /**
     * Cronjob for finish orders confirm
     * @return void
     * @throws LocalizedException
     */
    public function execute(): void
    {
        $filter = $this->searchCriteriaBuilder
            ->addFilter('status', 'confirm')
            ->addFilter('updated_at', date('Y-m-d H:i:s', strtotime('-10 days')), 'lt')
            ->create();

        $orders = $this->orderRepository->getList($filter)->getItems();

        foreach ($orders as $order) {
            if ($order->getPayment()->getMethod() == Payment::CODE) {
                $this->invoiceOrder($order);
                $this->shipmentOrder($order);
                $this->orderRepository->save($order);
            }
        }
    }

    /**
     * @param $order
     * @return void
     * @throws LocalizedException
     * @throws \Exception
     */
    protected function invoiceOrder($order)
    {
        if (!$order->canInvoice()) {
            return;
        }
        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->register();
        $invoice->getOrder()->setIsInProcess(true);
        $transactionSave = $this->transaction->addObject(
            $invoice
        )->addObject(
            $invoice->getOrder()
        );
        $transactionSave->save();
    }

    /**
     * @param $order
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function shipmentOrder($order)
    {
        $shipment = $this->orderConverter->toShipment($order);
        foreach ($order->getAllItems() as $orderItem) {
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }
            $qtyShipped = $orderItem->getQtyToShip();
            $shipmentItem = $this->orderConverter->itemToShipmentItem($orderItem)->setQty($qtyShipped);
            $shipment->addItem($shipmentItem);
        }
        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);
        try {
            $transactionSave = $this->transaction->addObject(
                $shipment
            )->addObject(
                $shipment->getOrder()
            );
            $transactionSave->save();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
