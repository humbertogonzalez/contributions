<?php

namespace Psa\PsaPayment\Block\Checkout;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\QuoteFactory;

/**
 * Class Failure - Brief description of class objective
 * @package  code\Psa\PsaPayment\Block\Checkout
 */
class Failure extends Template
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * Failure construct
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(Context $context, Session $checkoutSession, QuoteFactory $quoteFactory)
    {
        parent::__construct($context);
        $this->quoteFactory = $quoteFactory;
        $this->checkoutSession = $checkoutSession;
        $this->setTemplate('failure.phtml');
    }

    /**
     * @throws Exception
     */
    public function persistCartSession() {
        $order = $this->checkoutSession->getLastRealOrder();
        $quote = $this->quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());

        if ($quote->getId()) {
            $quote->setIsActive(true)->setReservedOrderId(null)->save();
            $this->checkoutSession->replaceQuote($quote);
        }
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
