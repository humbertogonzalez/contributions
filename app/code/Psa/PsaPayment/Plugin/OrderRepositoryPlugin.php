<?php
/**
 * Copyright (c) 2023 - BalloonGroup (https://www.balloon-group.com/)
 * @author BalloonGroup
 * @category BallonGroup
 * @module BalloonGroup/PsaPayment
 */
namespace BalloonGroup\PsaPayment\Plugin;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class OrderRepositoryPlugin - Plugin to add custom attributes to API Orders
 * @package  BalloonGroup\PsaPayment\Plugin
 */
class OrderRepositoryPlugin
{
    /**
     * Order code field name
     */
    protected const ORDER_CODE = 'order_code';

    /**
     * Payment code field name
     */
    protected const PAYMENT_CODE = 'payment_code';

    /**
     * Order Extension Attributes Factory
     *
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;

    /**
     * OrderRepositoryPlugin constructor
     *
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(OrderExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * Add "order_code" nad "payment_code" extension attribute to order data object to make it accessible in API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $this->setExtensionAttributes($order);
        return $order;
    }
    /**
     * Add "order_code" and "payment_code extension attribute to order data object to make it accessible in Magento API data
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     *
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $searchResult)
    {
        $orders = $searchResult->getItems();
        foreach ($orders as &$order) {
            $this->setExtensionAttributes($order);
        }
        return $searchResult;
    }

    /**
     * @param OrderInterface $order
     * @return void
     */
    public function setExtensionAttributes(OrderInterface $order): void
    {
        $orderCode = $order->getData(self::ORDER_CODE);
        $paymentCode = $order->getData(self::PAYMENT_CODE);
        $extensionAttributes = $order->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
        $extensionAttributes->setOrderCode($orderCode);
        $extensionAttributes->setPaymentCode($paymentCode);
        $order->setExtensionAttributes($extensionAttributes);
    }
}
