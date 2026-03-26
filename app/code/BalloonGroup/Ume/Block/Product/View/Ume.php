<?php

declare(strict_types=1);

namespace BalloonGroup\Ume\Block\Product\View;

use Magento\Catalog\Block\Product\View\Description;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use BalloonGroup\Ume\Model\ResourceModel\Ume\CollectionFactory;

class Ume extends Template
{
    /**
     * Ume constructor
     *
     * @param Context $context
     * @param Description $productDescriptionBlock
     * @param CurrencyFactory $currencyFactory
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        private Description $productDescriptionBlock,
        private CurrencyFactory $currencyFactory,
        private StoreManagerInterface $storeManager,
        private readonly CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get product data
     *
     * @return ProductInterface
     */
    public function getProduct(): ProductInterface
    {
        return $this->productDescriptionBlock->getProduct();
    }

    /**
     * Get product ume
     *
     * @return string|null
     */
    public function getProductUme(): ?string
    {
        $ume = $this->getProduct()->getData('medida_estadistica');

        return ($ume !== null && $ume !== '') ? (string)$ume : null;
    }

    /**
     * Get Ume from Collection
     *
     * @return mixed
     */
    public function getUmeFromCollection(): mixed
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('sku', ['eq' => $this->getProduct()->getSku()]);

        return $collection->getFirstItem();
    }

    /**
     * Get product price
     */
    public function getProductPrice()
    {
        return $this->getProduct()->getPrice();
    }

    /**
     * Calculate priceList/ume = unitPrice
     *
     * @param float $ume
     * @return string
     * @throws NoSuchEntityException
     */
    public function calculateUmePrice(float $ume): string
    {
        return $this->getFormattedPrice($this->getProductPrice()/$ume, $this->getStoreCurrencyCode(), 2);
    }

    /**
     * Format price
     *
     * @param float $price
     * @param string $currencyCode
     * @param int $precision
     * @return string
     */
    public function getFormattedPrice(float $price, string $currencyCode, int $precision = 4): string
    {
        $currency = $this->currencyFactory->create()->load($currencyCode);
        $currencySymbol = $currency->getCurrencySymbol();

        return $currency->format(
            $price,
            ['symbol' => $currencySymbol, 'precision' => $precision],
            false,
            false
        );
    }

    /**
     * Separate UME value from product into ume and measure
     *
     * @param string $ume
     * @return array
     */
    public function separateUmeData(string $ume): array
    {
        // Match number (with comma or dot) followed by letters/digits
        if (preg_match('/^([\d.,]+)([a-zA-Z0-9]+)$/', $ume, $matches)) {
            $ume = $matches[1];
            if (str_contains($ume, ",")) {
                $ume = str_replace(',', '.', str_replace('.', '', $ume));
            }

            $unit = $matches[2];

            return [
                $ume,
                $unit
            ];
        }

        return [];
    }

    /**
     * Get store currency code
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStoreCurrencyCode(): string
    {
        $store = $this->storeManager->getStore();

        return $store->getCurrentCurrencyCode();
    }

    /**
     * Return ume unit
     *
     * @param string $attributeUnit
     * @return string
     */
    public function getUmeUnit(string $attributeUnit): string
    {
        return match ($attributeUnit) {
            'BI' => 'LITRO',
            'BO' => 'LITRO',
            'CJ' => 'UN',
            'HO' => 'HO',
            'KG' => 'KG',
            'LA' => 'KG',
            'M2' => 'M2',
            'PE' => 'PE',
            'PQ' => 'KG',
            'RE' => 'KG',
            'RO' => 'M2',
            'TR' => 'KG',
            'UN' => 'M2',
            'LT' => 'LITRO',
            default => 'M2',
        };
    }
}
