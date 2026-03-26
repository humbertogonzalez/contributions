<?php

declare(strict_types=1);

namespace BalloonGroup\DistributesDiscount\Model\Quote;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\SalesRule\Model\Validator;
use Magento\Customer\Model\Session;
use BalloonGroup\Distributes\Model\SerialFactory;
use BalloonGroup\Distributes\Model\ResourceModel\Serial;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;

class Discount extends AbstractTotal
{
    /**
     * Discount constructor
     *
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Validator $validator
     * @param Session $session
     * @param SerialFactory $serialFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param ScopeConfigInterface $scopeConfig
     * @param CheckoutSession $checkoutSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected readonly ManagerInterface $eventManager,
        protected readonly StoreManagerInterface $storeManager,
        protected readonly Validator $validator,
        protected Session $session,
        protected readonly SerialFactory $serialFactory,
        protected readonly PriceCurrencyInterface $priceCurrency,
        protected readonly ScopeConfigInterface $scopeConfig,
        protected CheckoutSession $checkoutSession,
        protected LoggerInterface $logger
    )
    {
        $this->setCode('canje_discount');
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): Discount
    {
        $this->logger->info("===== Discount::collect =====");
        parent::collect($quote, $shippingAssignment, $total);

        //code to make sure we don't double deduct or add our value
        $address = $shippingAssignment->getShipping()->getAddress();
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            $this->logger->info("> Return empty count _getAddressItems...");
            return $this;
        }

        $label = '';
        $serial = $this->session->getSerie();
        $this->logger->info(print_r($serial, true));
        $data = $this->session->getData();
        //$canjeRepuestos = $this->session->getCanje();
        $canjeRepuestos = $this->checkoutSession->getCanje();
        $isPurificador = $this->checkoutSession->getIsPurificador();
        $this->logger->info("> CanjeRepuestos: " . $canjeRepuestos);
        $this->logger->info("> isPurificador: " . $isPurificador);

        if (!empty($canjeRepuestos) && $canjeRepuestos == "canje") {
            $canjeDiscount = $this->getAmountToDiscount($quote);
            if (!empty($serial)) {
                $this->logger->info("> Not empty serial" );
                $discountCanje = $this->getSerialCanje($serial['code_serial']);
                $discountPercent = (int)$this->scopeConfig->getValue('distributes_canje/general/discount');
                $label = 'Descuento '.$discountPercent.'% '.$serial['code_serial'];
                $totalDiscount = $canjeDiscount * $discountPercent/ 100;
                $discountAmount ="-".$totalDiscount;
                $appliedCartDiscount = 0;
                if($total->getDiscountDescription())
                {
                    $appliedCartDiscount = $total->getDiscountAmount();
                    $discountAmount = $total->getDiscountAmount() + $discountAmount;
                    $label = $total->getDiscountDescription().', '.$label;
                }

                $this->logger->info("> Label:" . $label);
                $this->logger->info("> DiscountAmount:" . $discountAmount);

                $total->setDiscountDescription($label);
                $total->setDiscountAmount($discountAmount);
                $total->setBaseDiscountAmount($discountAmount);
                $total->setSubtotalWithDiscount($total->getSubtotal() );
                $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal());

                if(isset($appliedCartDiscount))
                {
                    $total->addTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
                    $total->addBaseTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
                }
                else
                {
                    $total->setTotalAmount($this->getCode(), $discountAmount);
                    $total->setBaseTotalAmount($this->getCode(), $discountAmount);
                }
            }
        } elseif(!empty($isPurificador)) {
            $this->logger->info("> Is Purificador...");
            $canjeDiscount = $this->getAmountToDiscount($quote);

            //if (!empty($serial)) {
            $discountPercent = (int)$this->scopeConfig->getValue('distributes_canje/general/discount');
            $label = 'Descuento ' . $discountPercent . '% Purificadores';
            $totalDiscount = $canjeDiscount * $discountPercent / 100;
            $discountAmount = "-" . $totalDiscount;
            $appliedCartDiscount = 0;
            if ($total->getDiscountDescription()) {
                $appliedCartDiscount = $total->getDiscountAmount();
                $discountAmount = $total->getDiscountAmount() + $discountAmount;
                $label = $total->getDiscountDescription() . ', ' . $label;
            }

            $this->logger->info("> Label:" . $label);
            $this->logger->info("> DiscountAmount:" . $discountAmount);

            $total->setDiscountDescription($label);
            $total->setDiscountAmount($discountAmount);
            $total->setBaseDiscountAmount($discountAmount);
            $total->setSubtotalWithDiscount($total->getSubtotal());
            $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal());

            if (isset($appliedCartDiscount)) {
                $total->addTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
                $total->addBaseTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
            } else {
                $total->setTotalAmount($this->getCode(), $discountAmount);
                $total->setBaseTotalAmount($this->getCode(), $discountAmount);
            }
            //}
        }

        return $this;
    }

    private function getSerialCanje($serial) {
        $baseSerial = $this->serialFactory->create();
        $dataSerial=$baseSerial->load($serial,'code_serial')->getData();
        if ($dataSerial) {
            //$statusCanje = $dataSerial['status'];
            $id = $dataSerial['serial_id'];
            $data=$baseSerial->load($id,'serial_id')->getData();
            $data['status'] = 1;
            $baseSerial->setData($data);
            $baseSerial->save();
            $data=$baseSerial->load($id,'serial_id')->getData();
            $this->session->setSerie($data);
            $data['canje'] = 1;
            $dataSerial = $data;
        }
        return $dataSerial;
    }

    protected function getAmountToDiscount(Quote $quote) {
        $total = 0;
        $listo = false;

        if(!$quote->getItems()) {
            return $total;
        }
        foreach ($quote->getItems() as $item) {
            if (!$item->getCanje()) {
                continue;
            }
            $canjeQty = (int)$item->getQtyCanje();
            if($canjeQty === 0) {
                continue;
            }
            // $allQty = $item->getQty();
            //  if ($allQty < $canjeQty) {
            //      $canjeQty = $allQty;
            //  }
            return $item->getPrice();
            //$total += $canjeQty * $price;
        }
        return $total;
    }

    public function fetch (Quote $quote, Total $total): ?array
    {
        $result = null;
        $amount = $total->getDiscountAmount();

        if ($amount != 0)
        {
            $description = $total->getDiscountDescription();
            $result = [
                'code' => $this->getCode(),
                'title' => strlen($description) ? __( $description) : __('Discount'),
                'value' => $amount
            ];
        }
        return $result;
    }
}
