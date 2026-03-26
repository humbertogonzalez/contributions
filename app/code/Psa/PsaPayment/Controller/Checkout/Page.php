<?php

namespace Psa\PsaPayment\Controller\Checkout;

use Psa\Orders\Setup\Patch\Data\AddNewOrderStatusPatch;
use Psa\Distributes\Helper\Data;
use Psa\PsaPayment\Model\Payment;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Page - Brief description of class objective
 * @package  Psa\PsaPayment\Controller\Checkout
 */
class Page extends Action implements HttpGetActionInterface
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var Data
     */
    private $distributesHelper;

    /**
     * Page constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param OrderFactory $orderFactory
     * @param OrderSender $orderSender
     * @param Payment $payment
     * @param LoggerInterface $logger
     * @param Data $distributesHelper
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderFactory $orderFactory,
        OrderSender $orderSender,
        Payment $payment,
        LoggerInterface $logger,
        Data $distributesHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->orderSender = $orderSender;
        $this->payment = $payment;
        $this->logger = $logger;
        $this->distributesHelper = $distributesHelper;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->logger->info("===== PsaPaymentCheckout::Page =====");
        try {
            $request = $this->getRequest();
            $params = $request->getParams();
            $this->logger->info(print_r($params, true));
            $order = $this->_getOrder();

            $dealerName = $order->getShippingAddress()->getLastname();
            $dealerEmail = $this->payment->getDealerEmailByName($dealerName);

            if (isset($params['paymentOrder'])) {
                $order->setOrderCode($params['paymentOrder']);
                $atmOrder = $this->payment->getPaymentOrder($order);
                if (isset($atmOrder['status']) && $atmOrder['status'] >= 200 && $atmOrder['status'] < 300) {
                    $response = $atmOrder['response'];
                    $this->logger->info(print_r($response, true));
                    if ($response['state'] == 'paid') {
                        $this->logger->info("> PaymentState is paid: " . $response['state']);
                        $paymentCode = $response['payments'][0]['code'] ?? '';
                        $this->logger->info("> PaymentCode: " . $paymentCode);

                        foreach ($response['payments'] as $payments) {
                            if($payments['state'] == "accepted") {
                                $paymentCode = $payments['code'] ?? '';
                            }
                        }

                        $this->logger->info("> PaymentCode 2: " . $paymentCode);
                        $this->dispatchSuccessActionObserver();
                        $order->setState(Order::STATE_PROCESSING)->setStatus(AddNewOrderStatusPatch::STATUS_PAY_CODE);
                        $order->setPaymentCode($paymentCode);
                        $order->save();
                        if (!$order->getEmailSent()) {
                            $this->orderSender->send($order, true);
                        }

                        if($dealerEmail) {
                            $this->distributesHelper->sendEmailDealers($order, $dealerEmail, $dealerName);
                        }
                        $this->_redirect('checkout/onepage/success');
                    } else {
                        $this->cancelOrder($order);
                        $this->_redirect('checkout/onepage/failure');
                    }
                } else {
                    $this->cancelOrder($order);
                    $this->_redirect('checkout/onepage/failure');
                }
            } else {
                $this->cancelOrder($order);
                $this->_redirect('checkout/onepage/failure');
            }
        } catch (Exception $e) {
            $this->logger->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    protected function _getOrder()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        return $this->orderFactory->create()->loadByIncrementId($order->getIncrementId());
    }

    /**
     * Dispatch checkout_onepage_controller_success_action
     */
    public function dispatchSuccessActionObserver()
    {
        $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            [
                'order_ids' => [$this->_getOrder()->getId()],
                'order' => $this->_getOrder()
            ]
        );
    }

    /**
     * @param mixed $order
     * @return void
     */
    public function cancelOrder(mixed $order): void
    {
        $order->cancel();
        $order->setStatus(AddNewOrderStatusPatch::STATUS_PAY_CANCEL_CODE);
        $order->setState(Order::STATE_CANCELED);
        $order->save();
    }
}
