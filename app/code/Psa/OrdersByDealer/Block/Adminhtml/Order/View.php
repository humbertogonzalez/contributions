<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace BalloonGroup\OrdersByDealer\Block\Adminhtml\Order;

use BalloonGroup\Orders\Setup\Patch\Data\AddNewOrderStatusPatch;
use Magento\Sales\Model\ConfigInterface;
use Magento\Backend\Block\Widget\Form\Container;

/**
 * Adminhtml sales order view
 */
class View extends Container
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
        parent::__construct($context, $data);
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
        $this->setId('sales_dealer_order_view');
        $order = $this->getOrder();

        if ($this->_isAllowedAction('BalloonGroup_OrderAttachments::delivery') &&
            $order->getStatus() == AddNewOrderStatusPatch::STATUS_INVOICE_CODE) {
            $message = __('Estas seguro que quieres confirmar la entrega de la orden?');
            $this->addButton(
                'ship_order_psa',
                [
                    'label' => __('Confirmar entrega'),
                    'class' => __('ship-order-psa'),
                    'onclick' => "confirmSetLocation('{$message}', '{$this->getShipConfirmUrl()}')"
                ]
            );
        }

        if (!$order) {
            return;
        }
    }

    /**
     * Retrieve order model object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('sales_order');
    }

    /**
     * Retrieve Order Identifier
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getOrder() ? $this->getOrder()->getId() : null;
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $_extOrderId = $this->getOrder()->getExtOrderId();
        if ($_extOrderId) {
            $_extOrderId = '[' . $_extOrderId . '] ';
        } else {
            $_extOrderId = '';
        }
        return __(
            'Order # %1 %2 | %3',
            $this->getOrder()->getRealOrderId(),
            $_extOrderId,
            $this->formatDate(
                $this->_localeDate->date(new \DateTime($this->getOrder()->getCreatedAt())),
                \IntlDateFormatter::MEDIUM,
                true
            )
        );
    }

    /**
     * @return string
     */
    public function getShipConfirmUrl(){
        return $this->getUrl('psaprocess/order/ship',['order_id' => $this->getOrder()->getId()]);
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Return back url for view grid
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('sales_dealer/grid/index');
    }
}
