<?php

namespace Psa\PsaSpareParts\Controller\ProductsBySerial;

use Psa\Distributes\Controller\Change\ProductsBySerial;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Exception\NoSuchEntityException;

class Index extends ProductsBySerial
{
    /** @var int */
    public const CATEGORY_ID = 10;

    /**
     * @throws NoSuchEntityException
     */
    protected function getDataCanje($data): array
    {
        $this->logger->info("===== Repuestos::getDataCanje =====");
        if ($data['resultProduct']) {
            $this->session->setCanje('repuestos');
            $this->checkoutSession->setCanje('repuestos');
            $this->logger->info("> resultProduct True...");
            $product = $this->productRepository->get($data['codeProduct']);
            $data['products'] = [];
            $canjeProducts = [];

            foreach ($product->getRelatedProducts() as $repuesto) {
                $canjeProducts[] = $repuesto->getSku();
            }

            if (!$canjeProducts) {
                $this->logger->info("> Sin relatedProducts...");
                $data['resultWOProduct'] = $this->dataService->getConfig('distributes/textEdit/withoutSerial');

                $this->logger->info("> empty canjeProducts, show category...");
                $productCollection = $this->productCollectionFactory->create();
                $productCollection->addAttributeToSelect('*');
                $productCollection->addFieldToFilter('status', Status::STATUS_ENABLED);
                $productCollection->addCategoriesFilter(['in' => self::CATEGORY_ID]);
                $data = $this->getProductCollection($productCollection, $data);

                return $data;
            }
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addAttributeToSelect('*');
            $productCollection->addFieldToFilter('status', Status::STATUS_ENABLED);
            $productCollection->addFieldToFilter('sku', ['in' => $canjeProducts]);
            $data = $this->getProductCollection($productCollection, $data);
        } else {
            $this->logger->info("> Product Not Found");
            $data['resultProduct'] = true;
            $data['resultWOProduct'] = $this->dataService->getConfig('distributes/textEdit/withoutSerial');

            $this->logger->info("> empty resultProduct, show category...");
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addAttributeToSelect('*');
            $productCollection->addFieldToFilter('status', Status::STATUS_ENABLED);
            $productCollection->addCategoriesFilter(['in' => self::CATEGORY_ID]);
            $data = $this->getProductCollection($productCollection, $data);
        }

        return $data;
    }

    /**
     * @param string $urlKey
     * @param int $dipId
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getUrl(string $urlKey, int $dipId): string
    {
        return $this->storeManager->getStore()->getBaseUrl() . $urlKey . ".html?dipId={$dipId}";
    }

    /**
     * @param $productCollection
     * @param $data
     * @return array
     * @throws NoSuchEntityException
     */
    private function getProductCollection($productCollection, $data): array
    {
        if (empty($data['dipId'])) {
            $data['dipId'] = 0;
        }

        foreach ($productCollection->getItems() as $key => $product) {
            $data['products'][] = $product->getData();
            $data['products'][sizeof($data['products']) - 1]['url'] = $this->getUrl($product->getUrlKey(), $data['dipId']);
            $data['products'][sizeof($data['products']) - 1]['price'] = $this->getPrice(
                $product
            );
            $data['products'][sizeof($data['products']) - 1]['image'] = $product->getImage() ? $product->getImage() : '/placeholder' . ($this->dataService->getConfig('catalog/placeholder/thumbnail_placeholder') ?? '');
        }

        return $data;
    }
}
