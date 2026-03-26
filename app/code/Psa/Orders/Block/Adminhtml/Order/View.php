<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace BalloonGroup\Orders\Block\Adminhtml\Order;

use BalloonGroup\Orders\Setup\Patch\Data\AddNewOrderStatusPatch;
use Magento\Sales\Model\ConfigInterface;
use Magento\Sales\Model\Order;

/**
 * Adminhtml sales order view
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class View extends \Magento\Sales\Block\Adminhtml\Order\View
{
    /**
     * Block group
     *
     * @var string
     */
    protected $_blockGroup = 'Magento_Sales';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Sales config
     *
     * @var \Magento\Sales\Model\Config
     */
    protected $_salesConfig;

    /**
     * Reorder helper
     *
     * @var \Magento\Sales\Helper\Reorder
     */
    protected $_reorderHelper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ConfigInterface $salesConfig
     * @param \Magento\Sales\Helper\Reorder $reorderHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        ConfigInterface $salesConfig,
        \Magento\Sales\Helper\Reorder $reorderHelper,
        array $data = []
    ) {
        $this->_reorderHelper = $reorderHelper;
        $this->_coreRegistry = $registry;
        $this->_salesConfig = $salesConfig;
        parent::__construct(
            $context,
            $registry,
            $salesConfig,
            $reorderHelper,
            $data
        );
    }

    /**
     * Constructor
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _construct()
    {
        $this->_objectId = 'order_id';
        $this->_controller = 'adminhtml_order';
        $this->_mode = 'view';

        parent::_construct();

        $this->removeButton('delete');
        $this->removeButton('reset');
        $this->removeButton('save');
        $this->setId('sales_order_view');
        $order = $this->getOrder();

        if (!$order) {
            return;
        }

        if ($this->_isAllowedAction('Magento_Sales::actions_edit') && $order->canEdit()) {
            $onclickJs = 'jQuery(\'#order_edit\').orderEditDialog({message: \''
                . $this->getEditMessage($order) . '\', url: \'' . $this->getEditUrl()
                . '\'}).orderEditDialog(\'showDialog\');';

            $this->addButton(
                'order_edit',
                [
                    'label' => __('Edit'),
                    'class' => 'edit primary',
                    'onclick' => $onclickJs,
                    'data_attribute' => [
                        'mage-init' => '{"orderEditDialog":{}}',
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::cancel') && $order->canCancel()) {
            $this->addButton(
                'order_cancel',
                [
                    'label' => __('Cancel'),
                    'class' => 'cancel',
                    'id' => 'order-view-cancel-button',
                    'data_attribute' => [
                        'url' => $this->getCancelUrl()
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::emails') && !$order->isCanceled()) {
            $message = __('Are you sure you want to send an order email to customer?');
            $this->addButton(
                'send_notification',
                [
                    'label' => __('Send Email'),
                    'class' => 'send-email',
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getEmailUrl()}')"
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::creditmemo') && $order->canCreditmemo()) {
            $message = __(
                'This will create an offline refund. ' .
                'To create an online refund, open an invoice and create credit memo for it. Do you want to continue?'
            );
            $onClick = "setLocation('{$this->getCreditmemoUrl()}')";
            if ($order->getPayment()->getMethodInstance()->isGateway()) {
                $onClick = "confirmSetLocation('{$message}', '{$this->getCreditmemoUrl()}')";
            }
            $this->addButton(
                'order_creditmemo',
                ['label' => __('Credit Memo'), 'onclick' => $onClick, 'class' => 'credit-memo']
            );
        }

        // invoice action intentionally
        if ($this->_isAllowedAction('Magento_Sales::invoice') && $order->canVoidPayment()) {
            $message = __('Are you sure you want to void the payment?');
            $this->addButton(
                'void_payment',
                [
                    'label' => __('Void'),
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getVoidPaymentUrl()}')"
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::hold') && $order->canHold()) {
            $this->addButton(
                'order_hold',
                [
                    'label' => __('Hold'),
                    'class' => __('hold'),
                    'id' => 'order-view-hold-button',
                    'data_attribute' => [
                        'url' => $this->getHoldUrl()
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::unhold') && $order->canUnhold()) {
            $this->addButton(
                'order_unhold',
                [
                    'label' => __('Unhold'),
                    'class' => __('unhold'),
                    'id' => 'order-view-unhold-button',
                    'data_attribute' => [
                        'url' => $this->getUnholdUrl()
                    ]
                ]
            );
        }

        if ($this->_isAllowedAction('Magento_Sales::review_payment')) {
            if ($order->canReviewPayment()) {
                $message = __('Are you sure you want to accept this payment?');
                $this->addButton(
                    'accept_payment',
                    [
                        'label' => __('Accept Payment'),
                        'onclick' => "confirmSetLocation('{$message}', '{$this->getReviewPaymentUrl('accept')}')"
                    ]
                );
                $message = __('Are you sure you want to deny this payment?');
                $this->addButton(
                    'deny_payment',
                    [
                        'label' => __('Deny Payment'),
                        'onclick' => "confirmSetLocation('{$message}', '{$this->getReviewPaymentUrl('deny')}')"
                    ]
                );
            }
            if ($order->canFetchPaymentReviewUpdate()) {
                $this->addButton(
                    'get_review_payment_update',
                    [
                        'label' => __('Get Payment Update'),
                        'onclick' => 'setLocation(\'' . $this->getReviewPaymentUrl('update') . '\')'
                    ]
                );
            }
        }

        if ($this->_isAllowedAction('Magento_Sales::invoice') && $order->canInvoice()) {
            $_label = $order->getForcedShipmentWithInvoice() ? __('Invoice and Ship') : __('Invoice');
            $this->addButton(
                'order_invoice',
                [
                    'label' => $_label,
                    'onclick' => 'setLocation(\'' . $this->getInvoiceUrl() . '\')',
                    'class' => 'invoice'
                ]
            );
        }

        if ($this->_isAllowedAction(
                'Magento_Sales::ship'
            ) && $order->canShip() && !$order->getForcedShipmentWithInvoice()
        ) {
            $this->addButton(
                'order_ship',
                [
                    'label' => __('Ship'),
                    'onclick' => 'setLocation(\'' . $this->getShipUrl() . '\')',
                    'class' => 'ship'
                ]
            );
        }

        if ($this->_isAllowedAction(
                'Magento_Sales::reorder'
            ) && $this->_reorderHelper->isAllowed(
                $order->getStore()
            ) && $order->canReorderIgnoreSalable()
        ) {
            $this->addButton(
                'order_reorder',
                [
                    'label' => __('Reorder'),
                    'onclick' => 'setLocation(\'' . $this->getReorderUrl() . '\')',
                    'class' => 'reorder'
                ]
            );
        }

        if ($order->getStatus() == AddNewOrderStatusPatch::STATUS_INVOICE_CODE) {
            $message = __('Estas seguro que quieres confirmar la entrega de la orden?');
            $this->addButton(
                'ship_order_psa',
                [
                    'label' => __('Ship Confirm'),
                    'class' => __('ship-order-psa'),
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getShipConfirmUrl()}')"
                ]
            );
        }
    }

    /**
     * @return string
     */
    public function getShipConfirmUrl(){
        return $this->getUrl('psaprocess/order/ship',['order_id' => $this->getOrder()->getId()]);
    }
}
