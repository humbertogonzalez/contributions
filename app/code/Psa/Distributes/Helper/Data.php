<?php
declare(strict_types=1);

namespace Psa\Distributes\Helper;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Data
{
    /**
     * @var Curl
     */
    protected Curl $_curl;

    protected ManagerInterface $messageManager;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;

    /**
     * @var StateInterface
     */
    private StateInterface $inlineTranslation;
    private UrlInterface $backendUrl;

    protected LoggerInterface $logger;

    /**
     * @param Curl $curl
     * @param ManagerInterface $messageManager
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param UrlInterface $backendUrl
     * @param LoggerInterface $logger
     */
    public function __construct(
        Curl $curl,
        ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        UrlInterface $backendUrl,
        LoggerInterface $logger
    ) {
        $this->_curl = $curl;
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->backendUrl = $backendUrl;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        $url = $this->getConfig('distributes/general/hostToken');
        $apiUrl = $this->getConfig('distributes/general/apiToken');
        return $url . $apiUrl;
    }

    /**
     * @return array
     */
    public function getToken(): array
    {
        $data = [];
        $url = $this->getApiUrl();
        $params = [
            'grant_type' => $this->getConfig('distributes/general/grant_type'),
            'client_secret' => $this->getConfig('distributes/general/client_secret'),//'5c06ae93-3502-4577-9586-ecdb57b715e9',
            'username' => $this->getConfig('distributes/general/username'),//'magento',
            'password' => $this->getConfig('distributes/general/password'),//'b8e065255d5326ea3cf1f85b0dd764f3',
            'client_id' => $this->getConfig('distributes/general/client_id') //'magento'
        ];
        $this->_curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->_curl->addHeader('Cookie', 'SERVERID=KEYCLOAK-01');
        try {
            $this->_curl->post($url, http_build_query($params));
            return json_decode($this->_curl->getBody(), true);
        } catch (\Exception $e) {
            $this->logger->error("> ERROR getToken: " . $e->getMessage());
            $data['error'] = $this->messageManager->addException($e, __('Can\'t connection service'));
            return $data;
        }
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $param
     * @return mixed
     */
    public function resultApi($param): mixed
    {
        $data = [];
        $token = $this->getToken();

        try {
            if (!empty($token['access_token'])) {
                $url = $param['url'] . $param['param'];
                $this->_curl->addHeader('Authorization', 'Bearer ' . $token['access_token']);
                $this->_curl->addHeader('Content-Type', 'application/json');
                $this->_curl->addHeader('X-ar.com.psa.em.traza', 'X-ar.com.psa.em.traza');
                $this->_curl->get($url);

                return json_decode($this->_curl->getBody());
            }
        } catch (\Exception $e) {
            $this->logger->error("> ERROR resultApi: " . $e->getMessage());
            $data['error'] = $this->messageManager->addException($e, __('Can\'t connection service'));
            return $data;
        }
        return false;
    }

    public function getRequest($url): mixed
    {
        $data = [];
        $token = $this->getToken();
        try {
            if (!empty($token['access_token'])) {
                $this->_curl->addHeader('Authorization', 'Bearer ' . $token['access_token']);
                $this->_curl->addHeader('Content-Type', 'application/json');
                $this->_curl->addHeader('X-ar.com.psa.em.traza', 'X-ar.com.psa.em.traza');
                $this->_curl->get($url);

                return json_decode($this->_curl->getBody());
            }
        } catch (\Exception $e) {
            $this->logger->error("> ERROR getRequest: " . $e->getMessage());
            $data['error'] = $this->messageManager->addException($e, __('Can\'t connection service'));
            return $data;
        }
        return false;
    }

    public function dipNumberSerie($serialProduct): mixed
    {
        $param['param'] = '?unidad.codigo=' . $serialProduct;
        $url = $this->getConfig('distributes/general/hostData');
        $endPoint = $this->getConfig('distributes/general/endPointDistSerie');
        $param['url'] = $url . $endPoint;
        return $this->resultApi($param);
    }

    public function verifyVar($obj, $var): mixed
    {
        if (!$obj) {
            return false;
        }
        if (is_array($obj)) {
            return false;
        }
        if (property_exists($obj, $var)) {
            return $obj->$var;
        } else {
            return false;
        }
    }


    public function getDealearsByBatch(int $currentPage = null, int $pageSize = null): mixed
    {
        $url = $this->getConfig('distributes/general/hostData');
        $endPoint = $this->getConfig('distributes/general/endPointDist');
        $param['url'] = $url . $endPoint;
        if ($currentPage && $pageSize) {
            $param['url'] .= '?paginar=' . $currentPage . ',' . $pageSize;
        }
        $dealers = $this->getRequest($param['url']);


        if (is_object($dealers) && !empty($dealers)) {
            $encabezado = $this->verifyVar($dealers, 'encabezado');
            if (!empty($encabezado->errores[0])) {
                $dealers->error = $encabezado->errores[0]->mensaje;
            } else {
                $dataHead = $dealers->encabezado;
                if (!empty($this->verifyVar($dataHead, 'errores'))) {
                    $dealers->error = $dealers->errores->mensaje;
                }
            }
            return $dealers;
        }
        return false;
    }

    public function productNumberSerie($serialProduct): mixed
    {
        $param['param'] = $serialProduct;
        $url = $this->getConfig('distributes/general/hostData');
        $endPoint = $this->getConfig('distributes/general/endPointProdSerie');
        $param['url'] = $url . $endPoint;
        return $this->resultApi($param);
    }

    /**
     * @param Order $order
     * @param $dealerEmail
     * @param $dealerName
     * @return void
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendEmailDealers(Order $order, $dealerEmail, $dealerName)
    {
        $this->logger->info("Distributes::sendEmailDealers =====");
        $this->logger->info("> IncrementID: " . $order->getIncrementId());
        $this->logger->info("> DealerEmail: " . $dealerEmail);
        $this->logger->info("> DealerName: " . $dealerName);

        $this->inlineTranslation->suspend();
        $templateOptions = [
            'area' => Area::AREA_FRONTEND,
            'store' => $this->storeManager->getStore()->getId()
        ];
        $templateVars = [
            'store' => $this->storeManager->getStore(),
            'order_id' => $order->getId(),
            'dealer_name' => $dealerName,
            'customer_name' => $order->getCustomerName(),
            'phone' => $order->getBillingAddress()->getTelephone(),
            'address' => $order->getBillingAddress()->getStreet()[0] . ', ' . $order->getBillingAddress()->getCity(),
            'customer_email' => $order->getCustomerEmail(),
            'items' => $order->getAllItems(),
            'admin_url' => $this->backendUrl->getUrl('sales_dealer/order/view', ['order_id' => $order->getId()])
        ];
        $from = [
            'email' => $this->getConfig('trans_email/ident_general/email'),
            'name' => $this->getConfig('trans_email/ident_general/name')
        ];
        $toEmail = $dealerEmail;
        $transport = $this->transportBuilder->setTemplateIdentifier('distribute_order_email')
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFromByScope($from, $this->storeManager->getStore()->getId())
            ->addTo($toEmail)
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }
}
