<?php

namespace BalloonGroup\PsaPayment\Controller\Checkout;

use Magento\Catalog\Controller\Product\View\ViewInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;

/**
 * Class Failure - Brief description of class objective
 * @package BalloonGroup\PsaPayment\Controller\Checkout
 */
class Failure extends Action implements ViewInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Failure constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(Context $context, ScopeConfigInterface $scopeConfig)
    {
        $this->context = $context;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_view->loadLayout(['default', 'atmpayment_checkout_failure']);
        $this->_view->renderLayout();
    }
}
