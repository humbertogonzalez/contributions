<?php

namespace Redegal\Middleware\Model\Repository;

use Psr\Log\LoggerInterface;
use GuzzleHttp\Exception\ClientException;
use Magento\Directory\Model\CountryFactory;
use Magento\SalesRule\Model\RuleRepository;
use Redegal\Middleware\Model\Config\Config;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Sales\Model\ResourceModel\Order\Tax\Item;
use Redegal\Middleware\Security\Middleware\MiddlewareAuthManager;
use Redegal\Middleware\Model\Repository\Request\SalesOrderRestRequest;
use Redegal\Middleware\Model\Repository\Factory\MiddlewareClientFactory;
use Redegal\Middleware\Model\Repository\Factory\MiddlewareRequestFactory;
use Redegal\Middleware\Model\Repository\SessionAwareMiddlewareRepository;
use Redegal\Middleware\Model\Client\Transformer\SalesOrderRestTransformer;
use Redegal\Middleware\Model\Repository\Factory\MiddlewareTransformerFactory;
use Magento\Sales\Model\ResourceModel\Order\Tax\CollectionFactory as TaxCollection;
use Redegal\Middleware\Model\Adapter\Db;
use Redegal\Middleware\Model\Email\SalesOrderErrorSender;

class SalesOrderMiddlewareRepository extends SessionAwareMiddlewareRepository
{
    const REGION_CONVERTER = [
        'CMX' => 'CDMX'
    ];

    const TAX_SHIPPING = 16.0000;
    const LET_DIFFERENCE_AMOUNT = 0.01;
    const LOG_FOLDER_PATH = 'orders';

    public function __construct(
        LoggerInterface $logger,
        MiddlewareClientFactory $clientFactory,
        MiddlewareRequestFactory $requestFactory,
        MiddlewareTransformerFactory $transformerFactory,
        MiddlewareAuthManager $authManager,
        TaxCollection $taxCollectionFactory,
        RuleRepository $ruleRepository,
        Item $taxItem,
        Config $config,
        CountryFactory $countryFactory,
        DirectoryList $dir,
        Db $db,
        SalesOrderErrorSender $sender,
        array $params = []
    ) {
        $this->taxCollectionFactory = $taxCollectionFactory;
        $this->taxItem = $taxItem;
        $this->ruleRepository = $ruleRepository;
        $this->config = $config;
        $this->countryFactory = $countryFactory;
        $this->dir = $dir;
        $this->db = $db;
        $this->sender = $sender;

        parent::__construct($logger, $clientFactory, $requestFactory, $transformerFactory, $authManager, $params);
    }


    public function sendSalesOrder($order)
    {
        try {
            return $this->getSalesOrder($order);
        } catch (\Exception $e) {
            if ($this->shouldUpdateCredentials($e)) {
                $this->authManager->updateCredentials();
                return $this->getSalesOrder($order);
            }
            $order->setStatus('error');//TODO: Change when decide the status
            $order->setState('processing');
            $order->save();
            $this->logger->critical("[Sales Order Service] Request to sales order service fail");
            $this->logger->critical(json_encode($e->getMessage()));
            // $this->logger->critical(json_encode($e->getTrace()));
            throw $e;
        }
    }

    private function getSalesOrder($order)
    {
        $options = [];
        $options['client_options'] = $this->getClientOptions($order);
        $options['body'] = $this->getSalesOrderRequestBody($order);
        $options['headers'] = ['Content-Type' => 'application/json', 'Content-Length' => strlen(json_encode($options['body']))];

        $this->logger->debug("[Sales Order Service] Request options: ".json_encode($options,JSON_PRETTY_PRINT));

        return $this->invoke(
            $options,
            SalesOrderRestRequest::class,
            SalesOrderRestTransformer::class
        );
    }

    private function getClientOptions($order)
    {
        $options = [];
        $options = $this->getCredentialsHeaders();
        $options['connection'] = 'keep-alive';
        $options['headers'] = [
            'content-type' => 'application/json',
        ];
        $options['fileId'] = $order->getIncrementId();
        $options['trace'] = true;
        $options['traceFolder'] = $this->dir->getPath('log').'/'.static::LOG_FOLDER_PATH;

        return $options;
    }

    private function getSalesOrderRequestBody($order)
    {
        $body = [
            'Header' => $this->getOrderInfo($order)
        ];

        $body['Header']['Detail'] = $this->getOrderItemsInfo($order);

        $body['Header']['ExtendedAmount'] = $this->sumOrderItemValues($body['Header']['Detail'], 'ExtendedAmount');

        if (!empty((float)$order->getBaseShippingAmount())) {
            $shippingItem = $this->getShippingItem($order);
            if (!empty($shippingItem)) {
                $body['Header']['Detail'][] = $shippingItem;
            }
        }

        $calculatedTotalAmount = $this->sumOrderItemValues($body['Header']['Detail'], 'TotalAmount');
        $diffBetweenCalculatedAndMagento = abs($body['Header']['TotalAmount'] - $calculatedTotalAmount);
        if ($diffBetweenCalculatedAndMagento > (float) static::LET_DIFFERENCE_AMOUNT) {
            $this->logger->critical("[Sales Order Service] The amount difference between the total amount of Magento and calculated is
                too large. Magento: ".$order->getBaseGrandTotal()." /Calculated: ".$calculatedTotalAmount);
        }

        $body['Header']['TotalAmount'] = $calculatedTotalAmount;

        return $body;
    }

    /**
     * Get order info for request
     *
     * @param Order $order
     * @return array
     */
    private function getOrderInfo($order)
    {
        $shippingAddress = $order->getShippingAddress();
        $taxes = $this->taxCollectionFactory->create()->loadByOrder($order);

        $indexTaxAmounts = [];
        foreach($taxes as $tax) {
            $indexTaxAmounts[$tax['code']] = $tax->getAmount();
        }

        $hasStreetInternalNumber = count($shippingAddress->getStreet()) == 7;

        return [
            'FinancialPartyDescription' => 'nutrisa',
            'CustomerID' => $order->getCustomerId() ?? '0',
            'CustomerName' => $shippingAddress->getFirstname() . " " . $shippingAddress->getLastname(),
            'CustomerEmail' => $order->getCustomerEmail(),
            'SalesOrderReference' => $order->getIncrementId(),
            'SalesOrderID' => $order->getEntityId(),
            'OrderPlaceDate' => $order->getCreatedAt(),
            'OrderCreatedDate' => $order->getCreatedAt(),
            'CountryCode' => $shippingAddress->getCountryId(),
            'StreetType' => $shippingAddress->getStreet()[0], // Tipo Calle
            'CountrySubDivisionCode' => static::REGION_CONVERTER[$shippingAddress->getRegionCode()] ?? $shippingAddress->getRegionCode(),
            'CityName' => $shippingAddress->getCity() ?? 'N/A',
            'CountyCode' => $shippingAddress->getRegion(), //State
            'CitySubDivisionName' => $hasStreetInternalNumber ? $shippingAddress->getStreet()[5] : $shippingAddress->getStreet()[4], //Colonia
            'CustomerAddress' => $this->getFullShippingAddress($shippingAddress),//Conjunto de todo
            'StreetName' => $shippingAddress->getStreet()[1] ?? 'N/A',//Street
            'BuildingNumber' => $shippingAddress->getStreet()[2] ?? 'N/A',//Número exterior
            'Unit' => $hasStreetInternalNumber ? $shippingAddress->getStreet()[3] : '',//Número interior
            'PostalCode' => $shippingAddress->getPostCode(),//CP
            'References' => $hasStreetInternalNumber ? $shippingAddress->getStreet()[6] : $shippingAddress->getStreet()[5],//Referencias
            'AddressReferences' => $hasStreetInternalNumber ? $shippingAddress->getStreet()[4] : $shippingAddress->getStreet()[3],//Entrecalles
            'Telephone1' => $shippingAddress->getTelephone(),
            'ShoppingCartID' => $order->getQuoteId(),
            'ShippingContact' => $this->getShippingCarrier($order),
            'Status' => $order->getStatus(),
            'ExtendedAmount' => $order->getSubtotal(),
            'Discount' =>  !empty((float)$order->getBaseDiscountAmount()) ? -1*$order->getBaseDiscountAmount() : 0,
            'IVATax' => $indexTaxAmounts['CI_IVA'] ?? 0,
            'IEPSTax' => $indexTaxAmounts['CI_Ieps1'] ?? 0,
            'TotalTaxes' => $order->getTaxAmount(),
            'TotalShipping' => $order->getBaseShippingAmount(),
            'TotalAmount' => $order->getBaseGrandTotal()
        ];
    }

    /**
     * Build order items info for request
     *
     * @param Order $order
     * @return array
     */
    private function getOrderItemsInfo($order)
    {
        $taxItems = $this->taxItem->getTaxItemsByOrderId($order->getId());

        $indexTaxItems = [];
        foreach($taxItems as $taxItem) {
            $indexTaxItems[$taxItem['item_id']][$taxItem['code']] = $taxItem;
        }

        $itemsInfo = [];
        foreach ($order->getAllItems() as $item)
        {
            $extendedAmount = round($item->getPrice()*$item->getQtyOrdered(), 2);
            $ruleCodes = $this->getCartRulesCodes($item);
            $itemsInfo[] = [
                "ItemMasterID" =>  $item->getSku(),
                "UnitPrice" => $item->getPrice(),
                "Quantity" =>  $item->getQtyOrdered(),
                "ExtendedAmount" => (float) $extendedAmount,
                "MarketingProgram" => !empty($ruleCodes) ? join(",", $ruleCodes) : "N/A", //TODO: Remove N/A when fix problem with empty value
                "PercentageMarketingProgram" => $this->getDiscountPercent($item),
                "AmountMarketingProgram" => $item->getDiscountAmount(),
                "TaxBaseAmount" => round((float)$extendedAmount-(float)$item->getDiscountAmount(), 4),//Total sin tax con descuento aplicado.
                "PercentageIVA" => isset($indexTaxItems[$item->getItemId()]['CI_IVA']) ? "16.0000" : 0,//TODO:Change this
                "IVATax" => $indexTaxItems[$item->getItemId()]['CI_IVA']['real_amount'] ?? 0,
                "PercentageIEPS" => $indexTaxItems[$item->getItemId()]['CI_Ieps1']['tax_percent'] ?? 0,
                "IEPSTax" => $indexTaxItems[$item->getItemId()]['CI_Ieps1']['real_amount'] ?? 0,
                "TotalTaxes" => $item->getTaxAmount(),
                "TotalAmount" => round(((float)$extendedAmount-(float)$item->getDiscountAmount())+(float)$item->getTaxAmount(), 4)
            ];
        }

        return $itemsInfo;
    }

    private function getFullShippingAddress($shippingAddress)
    {
        $hasStreetInternalNumber = count($shippingAddress->getStreet()) == 7;
        $streetType = $shippingAddress->getStreet()[0];
        $street = $shippingAddress->getStreet()[1] ?? '';
        $exteriorNumber= $shippingAddress->getStreet()[2] ?? '';
        $colonia = $hasStreetInternalNumber ? $shippingAddress->getStreet()[5] : $shippingAddress->getStreet()[4];
        $city = $shippingAddress->getCity();
        $state = $shippingAddress->getRegion();
        $country = $this->getCountryNameByCode($shippingAddress->getCountryId());

        return $streetType.' '.$street.' '.$exteriorNumber.', '.$colonia.', '.$city.', '.$state.', '.$country;
    }

    private function getCountryNameByCode($countryCode){
        $country = $this->countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

    private function sumOrderItemValues($items, $field)
    {
        $sum = 0;

        foreach($items as $item)
        {
            $sum += (float)$item[$field];
        }

        return round($sum, 4);
    }

    /**
     * Get the discount percent for an intem
     *
     * @param [type] $item
     * @return float
     */
    private function getDiscountPercent($item)
    {
        return empty((float)$item->getDiscountPercent()) && !empty((float)$item->getDiscountAmount())
            ? $this->calculateDiscountPercent((float)$item->getBaseRowTotal(), (float)$item->getDiscountAmount())
            : $item->getDiscountPercent();
    }

    private function getShippingItem($order)
    {
        $shippingAmount = (float) $order->getBaseShippingAmount();
        $shippingInclTaxAmount = (int) $order->getBaseShippingInclTax();
        $shippingTaxAmount = (float) $order->getBaseShippingTaxAmount();
        $shippingAmountSkuConfig = json_decode($this->getShippingAmountSkuConfig(), true);
        $shippingCarrier = mb_strtolower($this->getShippingCarrier($order));

        if (empty($shippingAmountSkuConfig)) {
            $this->logger->error("[Sales Order Service] The config for add shipping item is not set");
            return [];
        }

        $sku ='';

        foreach($shippingAmountSkuConfig as $configAmountSku) {
            $carrier = mb_strtolower(trim($configAmountSku['carrier']));
            $amount = trim($configAmountSku['amount']);

            if ($amount == $shippingInclTaxAmount && $carrier == $shippingCarrier) {
                $sku = trim($configAmountSku['sku']);
                break;
            }
        }

        if (empty($sku)) {
            $this->logger->error("[Sales Order Service] The config for shipping amount ". $shippingInclTaxAmount. " is not set" );
            return [];
        }

        return  [
            "ItemMasterID" => $sku,
            "UnitPrice" => $shippingAmount,
            "Quantity" =>  1,
            "ExtendedAmount" => $shippingAmount,
            "MarketingProgram" => "N/A",
            "PercentageMarketingProgram" => 0,
            "AmountMarketingProgram" => 0,
            "TaxBaseAmount" => $shippingAmount,
            "PercentageIVA" => self::TAX_SHIPPING,
            "IVATax" => $shippingTaxAmount,
            "PercentageIEPS" => 0,
            "IEPSTax" => 0,
            "TotalTaxes" => $shippingTaxAmount,
            "TotalAmount" => $shippingInclTaxAmount
        ];
    }

    /**
     * TODO: Add implementation to get Shipping carrier
     *
     * @param array $order
     * @return string
     */
    private function getShippingCarrier($order)
    {
        return 'DHL';

        $shippingAddress = $order->getShippingAddress();
        $postalCode = $shippingAddress->getPostCode();
        $rates = $this->searchPostalCodeInDB($postalCode);

        return !empty($rates) ? 'MOOVA' : 'DHL';
    }

    private function searchPostalCodeInDB($postalCode)
    {
        try {
            $connection = $this->db->getConnection();
            $select = $connection->select()
                ->from(['shipping_tablerate'=> $this->db->getTableName('shipping_tablerate')], [
                        'dest_zip'
                    ])
                ->where('dest_zip = ?', $postalCode);

            return $connection->fetchAll($select);
        } catch (\Exception $e) {
            $this->logger->critical("[Sales Order Service] Problem to search postal code in table rates");
            $this->logger->critical(json_encode($e->getMessage()));

            return [];
        }
    }

    /**
     * Get cart rule codes
     *
     * @param  Item $item
     * @return array
     */
    private function getCartRulesCodes($item)
    {
        $ruleIds = explode(",", $item->getAppliedRuleIds());
        $ruleCodes = [];
        foreach ($ruleIds as $id) {
            try {
                $ruleCodes[] = $this->ruleRepository->getById($id)->getDescription();
            } catch (\Exception $e) {
                $this->logger->info('[SalesOrderMiddlewareRepository] Not found rule with ID: '.$id);
            }

        }

        return $ruleCodes;
    }

    /**
     * Calculate the save percent in the price
     *
     * @param float $price
     * @param float $specialPrice
     * @return int
     */
    private function calculateDiscountPercent($total, $discountAmount)
    {
        if ($total) {
            return round(($discountAmount * 100) / $total , 2);
        }

        return 0;
    }

    private function getShippingAmountSkuConfig()
    {
        $env = $this->config->get('middleware/environment/env');
        return $this->config->get('middleware/'.$env.'/shipping_amount_sku');
    }
}
