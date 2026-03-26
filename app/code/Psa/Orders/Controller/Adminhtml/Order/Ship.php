<?php

namespace BalloonGroup\Orders\Controller\Adminhtml\Order;

use BalloonGroup\Orders\Setup\Patch\Data\AddNewOrderStatusPatch;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Backend\App\Action;

/**
 * Class Ship - Set state delivery and send email.
 * @package BalloonGroup/Orders/Controller/Adminhtml/Order
 */
class Ship extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'BalloonGroup_OrderAttachments::delivery';

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;

    /**
     * @var StateInterface
     */
    private StateInterface $inlineTranslation;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * Cancel constructor.
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($this->getRequest()->getParam('order_id'));
        try {
            $order->setState(Order::STATE_PROCESSING);
            $order->setStatus(AddNewOrderStatusPatch::STATUS_DELIVERY_CODE);
            $this->sendDeliveryEmail($order);
            $this->orderRepository->save($order);
            $this->messageManager->addSuccessMessage(__('Orden entregada'));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addErrorMessage(__('La orden no se puede entregar: %1', $e->getMessage()));
        }
        return $resultRedirect->setPath('sales_dealer/order/view', [ 'order_id' => $order->getId() ]);
    }

    /**
     * @param Order $order
     * @return void
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendDeliveryEmail(Order $order)
    {
        $this->inlineTranslation->suspend();
        $templateOptions = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $this->storeManager->getStore()->getId()
        ];
        $templateVars = [
            'store' => $this->storeManager->getStore(),
            'custom_email' => $this->scopeConfig->getValue(
                'trans_email/ident_custom1/email',
                ScopeInterface::SCOPE_STORE
            )
        ];
        $from = [
            'email' => $this->scopeConfig->getValue(
                'trans_email/ident_general/email',
                ScopeInterface::SCOPE_STORE
            ),
            'name' => $this->scopeConfig->getValue(
                'trans_email/ident_general/name',
                ScopeInterface::SCOPE_STORE
            )
        ];
        $toEmail = $order->getCustomerEmail();
        $transport = $this->transportBuilder->setTemplateIdentifier('shipped_order_customer_template')
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFromByScope($from, $this->storeManager->getStore()->getId())
            ->addTo($toEmail)
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }
}
