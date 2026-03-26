<?php

namespace BalloonGroup\PsaPayment\Controller\Checkout;

use BalloonGroup\PsaPayment\Model\Config\Config;
use BalloonGroup\PsaPayment\Model\Curl\RestClient;
use Exception;
use Magento\Catalog\Controller\Product\View\ViewInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Message\ManagerInterface;
use BalloonGroup\PsaPayment\Model\Payment;
use Psr\Log\LoggerInterface;

class Pay extends Action implements ViewInterface
{
    private const URL_PAY = '/public/payment/order/';

    /**
     * @var Payment
     */
    protected $payment;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * Pay constructor.
     * @param Context $context
     * @param Payment $payment
     * @param Config $config
     * @param ManagerInterface $messageManager
     * @param ResultFactory $resultFactory
     * @param RestClient $restClient
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Payment $payment,
        Config $config,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        RestClient $restClient,
        LoggerInterface $logger
    ) {
        $this->payment = $payment;
        $this->config = $config;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
        $this->restClient = $restClient;
        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface|bool
     * @throws NotFoundException
     */
    public function execute()
    {
        try {
            $array_assign = $this->payment->postPago();
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $code = $array_assign['response']['code'] ?? '';

            if ($array_assign['status'] > 200 && $array_assign['status'] < 300 && !empty($code)) {
                $url = $this->config->getGatewayUrl() . self::URL_PAY . $code;
                $params = [
                    "sign" => $this->restClient->getSign(),
                    "publicKey" => $this->config->getPublicKey()
                ];

                $this->logger->info("Params to redirect: " . json_encode($params));
                $url .= "?" . http_build_query($params);
                $resultRedirect->setUrl($url);
                $resultRedirect->setHeader('x-request-id', uniqid());
            } else {
                $this->messageManager->addErrorMessage(__('Please try again later.'));
                $resultRedirect->setUrl('/checkout/cart');
            }

            return $resultRedirect;
        } catch (Exception $e) {
            $this->logger->error("ERROR CONTROLLER BASIC PAY: " . $e->getMessage());
        }
    }
}
