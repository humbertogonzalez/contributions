<?php

namespace Psa\PsaPayment\Model;

use Psa\PsaPayment\Model\Config\Config;
use Psa\PsaPayment\Model\Curl\RestClient;
use Exception;
use Magento\Catalog\Helper\Image;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as customerSession;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory as LocationCollectionFactory;

/**
 * Class Payment - Brief description of class objective
 * @package  Psa\PsaPayment\Model
 */
class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'atm_psa';

    /**
     *  Self fields
     */
    protected $_scopeConfig;
    protected $_helperData;
    protected $_helperImage;
    protected $_checkoutSession;
    protected $_customerSession;
    protected $_orderFactory;
    protected $_urlBuilder;
    protected $_basic;

    /**
     *  Overrides fields
     */
    protected $_code = self::CODE;
    protected $_isGateway = true;
    protected $_canOrder = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canFetchTransactionInfo = true;
    protected $_canReviewPayment = true;
    protected $_infoBlockType = 'Psa\PsaPayment\Block\Info';
    protected $_isInitializeNeeded = true;

    /**
     * @var LoggerInterface
     */
    private $loggerPsa;
    private RestClient $restClient;
    private LocationCollectionFactory $locationCollectionFactory;

    /**
     * Payment constructor.
     * @param Image $helperImage
     * @param Session $checkoutSession
     * @param customerSession $customerSession
     * @param OrderFactory $orderFactory
     * @param UrlInterface $urlBuilder
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param LoggerInterface $loggerPsa
     * @param RestClient $restClient
     * @param LocationCollectionFactory $locationCollectionFactory
     * @param array $data
     */
    public function __construct(
        Image $helperImage,
        Session $checkoutSession,
        customerSession $customerSession,
        OrderFactory $orderFactory,
        UrlInterface $urlBuilder,
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        LoggerInterface $loggerPsa,
        RestClient $restClient,
        LocationCollectionFactory $locationCollectionFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            null,
            null,
            $data
        );

        $this->_helperImage = $helperImage;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        $this->_urlBuilder = $urlBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->loggerPsa = $loggerPsa;
        $this->restClient = $restClient;
        $this->locationCollectionFactory = $locationCollectionFactory;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function postPago(): array
    {
        try {
            $response = $this->makePreference();

            if ($response['status'] == 200 || $response['status'] == 201) {
                $this->loggerPsa->info("Array preference ok");
            } else {
                $message = __('An Error occurred on process of payment data. Please try again later.');

                $this->loggerPsa->info($message);
            }
            return $response;
        } catch (Exception $e) {
            $this->loggerPsa->error('Fatal Error: Model Basic Payment PostPago:' . $e->getMessage());
            return [];
        }
    }

    /**
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null)
    {
        $isActive = $this->_scopeConfig->getValue(
            Config::PATH_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (empty($isActive)) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    /**
     * @return Order
     * @throws Exception
     */
    protected function getOrderInfo()
    {
        $order = $this->_orderFactory->create()->loadByIncrementId($this->_checkoutSession->getLastRealOrderId());
        if (empty($order->getId())) {
            throw new Exception(__('Error on create preference - Exception on getOrderInfo'));
        }
        return $order;
    }

    /**
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        $successPage = $this->_scopeConfig->getValue(
            Config::SUCCESS_URL,
            ScopeInterface::SCOPE_STORE
        );

        $successUrl = $successPage ? 'atmpayment/checkout/success' : 'checkout/onepage/success';
        return $this->_urlBuilder->getUrl($successUrl, ['_secure' => true]);
    }

    /**
     * Make preference for payment
     * @return array
     */
    public function makePreference(): array
    {
        try {
            $order = $this->getOrderInfo();
            $dealerCode = $this->getDealerCodeByName($order->getShippingAddress()->getLastname());
            $body = [];
            $body['seller']['code'] = $dealerCode;
            $body['payer']['email'] = $order->getCustomerEmail();
            $body['payer']['full_name'] = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
            $body['amount'] = $order->getGrandTotal();
            $body['currency'] = $order->getOrderCurrencyCode();
            $body['concept'] = "Tienda PSA | Pago en cuenta " . $dealerCode;

            $response = $this->restClient->post('/api/v1/payment/order', $body);
            $this->loggerPsa->info('Response makePreference: ' . json_encode($response));
            $code = $response['response']['code'] ?? null;
            if ($code) {
                $order->setOrderCode($code);
                $order->save();
            }
            return $response;
        } catch (Exception $e) {
            $this->loggerPsa->error('Fatal Error: Model Basic Payment makePreference:' . $e->getMessage());
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param $code
     * @return array
     */
    public function getPaymentOrder($order): array
    {
        try {
            $this->_logger->info('getOrderCode: ' . $order->getOrderCode());
            $response = $this->restClient->get('/api/v1/payment/order/' . $order->getOrderCode());
            $this->loggerPsa->info('Response getPaymentOrder: ' . json_encode($response));
            return $response;
        } catch (Exception $e) {
            $this->loggerPsa->error('Fatal Error: Model Basic Payment getPaymentOrder:' . $e->getMessage());
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param string $dealerName
     * @return string
     */
    public function getDealerCodeByName(string $dealerName): string
    {
        $dealersCollection = $this->locationCollectionFactory->create();
        $dealersCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter(
                'name',
                ['eq' => $dealerName]
            )->setPageSize(1);
        $dealersItems = $dealersCollection->getItems();
        $dealerCode = '';
        foreach ($dealersItems as $dealer) {
            $dealerCode = $dealer->getCode();
        }
        return $dealerCode;
    }

    /**
     * @param string $dealerName
     * @return string
     */
    public function getDealerEmailByName(string $dealerName): string
    {
        $this->loggerPsa->info("===== PsaPayment::getDealerEmailByName =====");
        $dealersCollection = $this->locationCollectionFactory->create();
        $dealersCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter(
                'name',
                ['like' => $dealerName]
            )->setPageSize(1);
        $dealersItems = $dealersCollection->getItems();
        $dealerEmail = '';
        foreach ($dealersItems as $dealer) {
            $dealerEmail = $dealer->getEmail();
        }

        $this->loggerPsa->info("> DealerEmail: " . $dealerEmail);

        return $dealerEmail;
    }
}
