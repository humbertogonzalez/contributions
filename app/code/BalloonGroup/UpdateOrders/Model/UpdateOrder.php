<?php

declare(strict_types=1);

namespace BalloonGroup\UpdateOrders\Model;

use Exception;
use BalloonGroup\UpdateOrders\Api\UpdateOrderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Api\SearchCriteriaBuilder;

class UpdateOrder implements UpdateOrderInterface
{
    public const INCREMENT_ID_FIELD = "increment_id";
    public const SAP_INVOICE_FIELD = "sap_invoice";
    public const SAP_INVOICE_FILE_FIELD = "sap_invoice_file";
    public const FINALIZED_STATUS = "finalized";

    /**
     * UpdateOrder constructor
     *
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        protected SearchCriteriaBuilder $searchCriteriaBuilder,
        protected OrderRepositoryInterface $orderRepository
    )
    {
    }

    /**
     * Bulk update order invoice data
     *
     * @param OrderInterface $order
     * @return OrderInterface
     * @throws Exception
     */
    public function updateOrders(OrderInterface $order): OrderInterface
    {
        try {
            $this->validatePayload($order);
            $orderData = $this->getOrderByIncrementId($order[self::INCREMENT_ID_FIELD]);
            $orderData->setData('sap_invoice', $order->getExtensionAttributes()->getSapInvoice());
            $orderData->setData('sap_invoice_file', $order->getExtensionAttributes()->getSapInvoiceFile());

            if ($extensionAttributes = $orderData->getExtensionAttributes()) {
                $extensionAttributes->setData('sap_invoice', $order->getExtensionAttributes()->getSapInvoice());
                $extensionAttributes->setData('sap_invoice_file', $order->getExtensionAttributes()->getSapInvoiceFile());
            }

            $orderData->setStatus(self::FINALIZED_STATUS);
            $this->orderRepository->save($orderData);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $orderData;
    }

    /**
     * @param $order
     * @return bool
     * @throws LocalizedException
     */
    private function validatePayload($order): bool
    {
        if (isset(
            $order[self::INCREMENT_ID_FIELD], $order[self::SAP_INVOICE_FIELD], $order[self::SAP_INVOICE_FILE_FIELD]
        )) {
            if (empty($order[self::INCREMENT_ID_FIELD])) {
                throw new LocalizedException(
                    __(
                        "[ERROR]::El campo %1 es obligatorio [%2]",
                        self::INCREMENT_ID_FIELD,
                        $order[self::INCREMENT_ID_FIELD]
                    )
                );
            }

            if (empty($order[self::SAP_INVOICE_FIELD])) {
                throw new LocalizedException(
                    __(
                        "[ERROR]::El campo %1 es obligatorio [%2]",
                        self::SAP_INVOICE_FIELD,
                        $order[self::SAP_INVOICE_FIELD]
                    )
                );
            }

            if (empty($order[self::SAP_INVOICE_FILE_FIELD])) {
                throw new LocalizedException(
                    __(
                        "[ERROR]::El campo %1 es obligatorio [%2]",
                        self::SAP_INVOICE_FILE_FIELD,
                        $order[self::SAP_INVOICE_FILE_FIELD]
                    )
                );
            }
        }

        return true;
    }

    /**
     * @param string $incrementId
     * @return OrderInterface
     * @throws NoSuchEntityException
     */
    private function getOrderByIncrementId(string $incrementId): OrderInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            self::INCREMENT_ID_FIELD,
            $incrementId,
            'eq'
        )->create();

        $orders = $this->orderRepository->getList($searchCriteria)->getItems();

        foreach ($orders as $order) {
            return $order;
        }

        throw new NoSuchEntityException(__("The Order %1 does not exist.", $incrementId));
    }
}
