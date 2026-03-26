<?php

namespace Redegal\Tax\Model\Calculation;

use Magento\Tax\Api\Data\QuoteDetailsItemInterface;

/**
 * Class to override Magento to fix problem with taxes and discounts
 */
class UnitBaseCalculator extends \Magento\Tax\Model\Calculation\UnitBaseCalculator
{

    /**
     * {@inheritdoc}
     */
    protected function calculateWithTaxInPrice(QuoteDetailsItemInterface $item, $quantity, $round = true)
    {
        $taxRateRequest = $this->getAddressRateRequest()->setProductClassId(
            $this->taxClassManagement->getTaxClassId($item->getTaxClassKey())
        );
        $rate = $this->calculationTool->getRate($taxRateRequest);
        $storeRate = $storeRate = $this->calculationTool->getStoreRate($taxRateRequest, $this->storeId);

        // Calculate $priceInclTax
        $applyTaxAfterDiscount = $this->config->applyTaxAfterDiscount($this->storeId);
        $priceInclTax = $this->calculationTool->round($item->getUnitPrice());
        if (!$this->isSameRateAsStore($rate, $storeRate)) {
            $priceInclTax = $this->calculatePriceInclTax($priceInclTax, $storeRate, $rate, $round);
        }
        $uniTax = $this->calculationTool->calcTaxAmount($priceInclTax, $rate, true, false);
        $deltaRoundingType = self::KEY_REGULAR_DELTA_ROUNDING;
        if ($applyTaxAfterDiscount) {
            $deltaRoundingType = self::KEY_TAX_BEFORE_DISCOUNT_DELTA_ROUNDING;
        }
        $uniTax = $this->roundAmount($uniTax, $rate, true, $deltaRoundingType, $round, $item);
        $price = $priceInclTax - $uniTax;

        //Handle discount
        $discountTaxCompensationAmount = 0;
        $discountAmount = $item->getDiscountAmount();
        if ($applyTaxAfterDiscount) {
            //TODO: handle originalDiscountAmount
            $unitDiscountAmount = $discountAmount / $quantity;
            $taxableAmount = max($price - $unitDiscountAmount, 0); //REDEGAL:: Change $priceInclTax to price
            $unitTaxAfterDiscount = $this->calculationTool->calcTaxAmount(
                $taxableAmount,
                $rate,
                false, //REDEGAL:: Change true to false
                false
            );
            $unitTaxAfterDiscount = $this->roundAmount(
                $unitTaxAfterDiscount,
                $rate,
                true,
                self::KEY_REGULAR_DELTA_ROUNDING,
                $round,
                $item
            );

            // Set discount tax compensation
            $unitDiscountTaxCompensationAmount = $uniTax - $unitTaxAfterDiscount;
            $discountTaxCompensationAmount = $unitDiscountTaxCompensationAmount * $quantity;
            $uniTax = $unitTaxAfterDiscount;
        }
        $rowTax = $uniTax * $quantity;

        // Calculate applied taxes
        /** @var  \Magento\Tax\Api\Data\AppliedTaxInterface[] $appliedTaxes */
        $appliedRates = $this->calculationTool->getAppliedRates($taxRateRequest);
        $appliedTaxes = $this->getAppliedTaxes($rowTax, $rate, $appliedRates);

        return $this->taxDetailsItemDataObjectFactory->create()
            ->setCode($item->getCode())
            ->setType($item->getType())
            ->setRowTax($rowTax)
            ->setPrice($price)
            ->setPriceInclTax($priceInclTax)
            ->setRowTotal($price * $quantity)
            ->setRowTotalInclTax($priceInclTax * $quantity)
            ->setDiscountTaxCompensationAmount(0) //REDEGAL:: Change $unitDiscountTaxCompensationAmount to 0
            ->setAssociatedItemCode($item->getAssociatedItemCode())
            ->setTaxPercent($rate)
            ->setAppliedTaxes($appliedTaxes);
    }
}
