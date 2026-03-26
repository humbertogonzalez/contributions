<?php

namespace Redegal\Middleware\Cron;

use \Psr\Log\LoggerInterface;
use Redegal\Middleware\Model\Repository\SalesOrderMiddlewareRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Redegal\Middleware\Model\Email\SalesOrderErrorSender;

class SendOrders
{
    private $logger;

    public function __construct(
        LoggerInterface $logger,
        SalesOrderMiddlewareRepository $salesOrderMiddlewareRepository,
        CollectionFactory $orderCollectionFactory,
        SalesOrderErrorSender $sender
    ) {
        $this->logger = $logger;
        $this->salesOrderMiddlewareRepository = $salesOrderMiddlewareRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->sender = $sender;
    }

    public function execute()
    {
        $this->logger->info('[Send Orders Cron] Running `cron` from `Redegal\SendOrders`');
        $this->sendPayedOrdersToMiddleware();
        $this->logger->info('[Send Orders Cron] Finish `cron` from `Redegal\SendOrders`');
        return $this;
    }


    /**
     * Test ERP service to send processing orders
     *
     * @return void
     */
    private function sendPayedOrdersToMiddleware()
    {
        $orders = $this->orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('status',['processing', 'pending']);

        foreach($orders as $order) {
            try {
                $this->logger->debug('Processing order with Increment ID: '.$order->getIncrementId());
                $response = $this->salesOrderMiddlewareRepository->sendSalesOrder($order);
                $this->logger->debug('[Send Orders Cron] Transformed response: '. json_encode($response));
                $order->setStatus('sended');//TODO: Change when decide the status
                $order->setState('processing');
                $order->save();
            } catch (\Exception $e) {
                $this->sendOrderErrorEmail($order, $e->getMessage());
                $order->setStatus('error');//TODO: Change when decide the status
                $order->setState('processing');
                $order->save();
                $this->logger->critical('Error processing order: '.$e->getMessage());
            }
        }
    }

    private function sendOrderErrorEmail($order, $message='')
    {
        $this->sender->sendEmail($order, $message);
    }
}
