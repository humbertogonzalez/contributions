<?php

namespace BalloonGroup\PsaPayment\Block\Checkout;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Success - Brief description of class objective
 * @package  BalloonGroup\PsaPayment\Block\Checkout
 */
class Success extends \Magento\Framework\View\Element\Template
{

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var ScopeInterface
     */
    protected $scopeConfig;

    /**
     * @var Repository
     */
    protected $assetRepo;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @param Context $context
     * @param OrderFactory $orderFactory
     * @param Session $checkoutSession
     * @param ScopeConfigInterface $scopeConfig
     * @param Repository $assetRepo
     * @param QuoteFactory $quoteFactory
     * @param array $data
     */
    public function __construct(
        Context              $context,
        OrderFactory         $orderFactory,
        Session              $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        Repository           $assetRepo,
        QuoteFactory         $quoteFactory,
        array                $data = []
    ) {
        $this->orderFactory = $orderFactory;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->assetRepo = $assetRepo;
        $this->quoteFactory = $quoteFactory;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @throws Exception
     */
    public function persistCartSession()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $quote = $this->quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());

        if ($quote->getId()) {
            $quote->setIsActive(true)->setReservedOrderId(null)->save();
            $this->checkoutSession->replaceQuote($quote);
        }
    }

    /**
     * @return false|float|DataObject|OrderPaymentInterface|mixed|null
     */
    public function getPayment()
    {
        return $this->getOrder()->getPayment();
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        $orderIncrementId = $this->checkoutSession->getLastRealOrderId();
        return $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        $order = $this->getOrder();
        $total = $order->getBaseGrandTotal();

        if (!$total) {
            $total = ($order->getBasePrice() + $order->getBaseShippingAmount());
        }

        return $total;
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->getOrder()->getEntityId();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getPaymentMethod()
    {
        return $this->getPayment()->getMethodInstance()->getCode();
    }

    /**
     * Return url to go to order detail page
     *
     * @return string
     */
    public function getOrderUrl()
    {
        $params = ['order_id' => $this->checkoutSession->getLastRealOrder()->getId()];
        return $this->_urlBuilder->getUrl('sales/order/view', $params);
    }

    /**
     * @return string
     */
    public function getLogoATM()
    {
        return $this->_assetRepo->getUrl('BalloonGroup_PsaPayment::images/logo.png');
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getCheckoutUrl()
    {
        $this->persistCartSession();
        return $this->getUrl('checkout', ['_secure' => true]);
    }
}
