<?php

/**
 * Copyright (c) 2023 - BalloonGroup (https://www.balloon-group.com/)
 * @author BalloonGroup
 * @category BallonGroup
 * @module BalloonGroup/Orders
 */

namespace BalloonGroup\Orders\Setup\Patch\Data;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResource;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;

/**
 * Class AddNewOrderStatusPatch - Patch to add new status orders
 * @package  BalloonGroup\Orders\Setup\Patch
 */
class AddNewOrderStatusPatch implements DataPatchInterface
{
    public const STATUS_PAY_CODE = 'pay';
    public const STATUS_PAY_LABEL = 'Pagado';
    public const STATUS_PAY_CANCEL_CODE = 'pay_cancel';
    public const STATUS_PAY_CANCEL_LABEL = 'Pago Cancelado';
    public const STATUS_CONFIRM_CODE = 'confirm';
    public const STATUS_CONFIRM_LABEL = 'Confirmado';
    public const STATUS_INVOICE_CODE = 'invoice';
    public const STATUS_INVOICE_LABEL = 'Facturado';
    public const STATUS_DELIVERY_CODE = 'delivery';
    public const STATUS_DELIVERY_LABEL = 'Entregado';

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var StatusFactory
     */
    private StatusFactory $statusFactory;

    /**
     * @var StatusResourceFactory
     */
    private StatusResourceFactory $statusResourceFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param StatusFactory $statusFactory
     * @param StatusResourceFactory $statusResourceFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
    }

    /**
     * Do Upgrade.
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->addPayStatus();
        $this->addPayCancelStatus();
        $this->addConfirmStatus();
        $this->addInvoiceStatus();
        $this->addDeliveryStatus();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Create new order pay status and assign it to state processing
     *
     * @return void
     * @throws Exception
     */
    protected function addPayStatus()
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->statusResourceFactory->create();
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => self::STATUS_PAY_CODE,
            'label' => self::STATUS_PAY_LABEL,
        ]);
        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {
            return;
        }
        $status->assignState(Order::STATE_PROCESSING, false, true);
    }

    /**
     * Create new order pay cancel status and assign it to state canceled
     *
     * @return void
     * @throws Exception
     */
    protected function addPayCancelStatus()
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->statusResourceFactory->create();
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => self::STATUS_PAY_CANCEL_CODE,
            'label' => self::STATUS_PAY_CANCEL_LABEL,
        ]);
        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {
            return;
        }
        $status->assignState(Order::STATE_CANCELED);
    }

    /**
     * Create new order confirm status and assign it to state processing
     *
     * @return void
     * @throws Exception
     */
    protected function addConfirmStatus()
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->statusResourceFactory->create();
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => self::STATUS_CONFIRM_CODE,
            'label' => self::STATUS_CONFIRM_LABEL,
        ]);
        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {
            return;
        }
        $status->assignState(Order::STATE_PROCESSING);
    }

    /**
     * Create new order invoice status and assign it to state processing
     *
     * @return void
     * @throws Exception
     */
    protected function addInvoiceStatus()
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->statusResourceFactory->create();
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => self::STATUS_INVOICE_CODE,
            'label' => self::STATUS_INVOICE_LABEL,
        ]);
        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {
            return;
        }
        $status->assignState(Order::STATE_PROCESSING, false, true);
    }

    /**
     * Create new order delivery status and assign it to state processing
     *
     * @return void
     * @throws Exception
     */
    protected function addDeliveryStatus()
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->statusResourceFactory->create();
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => self::STATUS_DELIVERY_CODE,
            'label' => self::STATUS_DELIVERY_LABEL,
        ]);
        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {
            return;
        }
        $status->assignState(Order::STATE_PROCESSING, false, true);
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * Example of implementation:
     *
     * [
     *      \Vendor_Name\Module_Name\Setup\Patch\Patch1::class,
     *      \Vendor_Name\Module_Name\Setup\Patch\Patch2::class
     * ]
     *
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }
}
